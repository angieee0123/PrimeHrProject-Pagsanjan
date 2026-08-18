# Letterhead artwork

The HRMO's own letterhead, printed at the top of the Pass Slip and the
Certificate of Appearance. Registered in `config/cs_form.php` under
`letterhead`.

These files were lifted from the office's own `PASS SLIP (NEW).pdf`, so the
system prints the real letterhead rather than a rebuilt lookalike.

| File | What it is |
|---|---|
| `masthead.png` | The whole masthead in one piece — municipal seal, the blackletter wording, the flourish, "Human Resource Management Office" and the telephone line, and the HRMO seal. This is how the source document holds it. |
| `i-love-pagsanjan.png` | The "I ♥ PAGSANJAN" tourism mark, printed to the right of the masthead |
| `hrmo-logo.png` | The HRMO seal on its own — used **only** by the fallback below |

## The fallback

If `masthead.png` or `i-love-pagsanjan.png` is missing, the form **draws** the
masthead instead: the wording from `config/cs_form.php`, the municipal seal
from `SiteContentService`, `hrmo-logo.png` and the tourism mark beside it, in a
blackletter face. It is a close likeness, not the artwork.

That path exists so the form still prints on an install where these images were
never copied across. Both images are required for the real letterhead — with
one of them missing the form draws the whole masthead rather than printing half
of one.

## Consequences worth knowing

- **The seal in `masthead.png` does not follow Admin → Website Content.**
  Everywhere else in the system the seal comes from `SiteContentService`, so
  uploading a new one updates the website, the sidebars and the payslips. It
  will *not* change these two forms, because their seal is baked into the
  office's letterhead artwork. Re-cut `masthead.png` to change it.
- Replacing any of these files changes the printed form immediately — no code
  change, no restart. Keep the filenames, or update `config/cs_form.php`.
- PNG on a white background. The forms print on white, so a flat background
  avoids depending on how a PDF renderer handles alpha.
