<?php

namespace App\Services;

/**
 * The password a new employee account is created with when nobody types one.
 *
 * The Add Employee wizard used to ask the admin to type a password and confirm
 * it. That value was chosen by hand — normally reused from the last account the
 * same admin created, or the LGU's usual pattern — and then mailed in plaintext.
 * One leaked credentials email therefore described the shape of the next hire's
 * login as well. The wizard now types nothing: the server invents the password,
 * hashes it, and the only copy that ever exists in readable form is the one in
 * the credentials email the employee receives.
 *
 * Two properties this class exists to guarantee:
 *
 * - **It comes from the CSPRNG.** `random_int()` (and a Fisher-Yates pass built
 *   on it), never `rand()`, `mt_rand()` or `str_shuffle()` — those are seeded
 *   PRNGs, and another output of the same generator is enough to predict the
 *   next password.
 * - **It is a password this system would accept.** At least one uppercase
 *   letter, one lowercase letter and one digit, and `LENGTH` characters, so the
 *   server's `min:8` rule and the complexity the other password screens ask a
 *   person for are both satisfied by the generated one.
 *
 * The character pools drop what people misread when they retype a password out
 * of an email — `I`/`l`/`1` and `O`/`0`. That costs a little entropy and saves
 * the support call where a correct password is rejected because one glyph was
 * read as another.
 */
class TemporaryPasswordService
{
    /**
     * The length of a generated password.
     *
     * Read by the wizard's copy rather than repeated there, so the sentence the
     * admin reads cannot promise a length the generator does not produce.
     */
    public const LENGTH = 8;

    /**
     * One character from each pool is guaranteed, so a shorter password than
     * this could not honour that promise.
     */
    private const MIN_LENGTH = 3;

    /** No `I` or `O` — they read as `1` and `0`. */
    private const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

    /** No lowercase `l` — it reads as `1`. */
    private const LOWERCASE = 'abcdefghijkmnopqrstuvwxyz';

    /** No `0` or `1` — they read as `O` and `l`. */
    private const DIGITS = '23456789';

    /**
     * A fresh plaintext password. Callers hash it immediately; nothing stores
     * what this returns.
     */
    public static function generate(int $length = self::LENGTH): string
    {
        $length = max(self::MIN_LENGTH, $length);

        $characters = [
            self::pick(self::UPPERCASE),
            self::pick(self::LOWERCASE),
            self::pick(self::DIGITS),
        ];

        $pool = self::UPPERCASE . self::LOWERCASE . self::DIGITS;

        while (count($characters) < $length) {
            $characters[] = self::pick($pool);
        }

        return implode('', self::shuffleCharacters($characters));
    }

    private static function pick(string $pool): string
    {
        return $pool[random_int(0, strlen($pool) - 1)];
    }

    /**
     * Fisher-Yates, driven by `random_int()`.
     *
     * The guaranteed characters above are drawn first, so without this they
     * would always sit in positions 1-3 and every password this system issues
     * would share that structure. `shuffle()` cannot be used for the same
     * reason the pools use `random_int()`: it is seeded by the Mersenne
     * Twister, not the CSPRNG.
     *
     * @param  array<int, string>  $characters
     * @return array<int, string>
     */
    private static function shuffleCharacters(array $characters): array
    {
        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);

            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }

        return $characters;
    }
}
