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
| `pusong-pagsanjan.png` | The "Puso ng Pagsanjan" mark closing the **Travel Order**'s footer. Not shipped: drop the office's own artwork in here and the footer picks it up on the next render — no code change, no restart. Registered under `cs_form.travel_order.footer.logo` |

## The fallback

If `masthead.png` or `i-love-pagsanjan.png` is missing, the form **draws** the
masthead instead: the wording from `config/cs_form.php`, the municipal seal
from `SiteContentService`, `hrmo-logo.png` and the tourism mark beside it, in a
blackletter face. It is a close likeness, not the artwork.

That path exists so the form still prints on an install where these images were
never copied across. Both images are required for the real letterhead — with
one of them missing the form draws the whole masthead rather than printing half
of one.

## The Travel Order wears a different masthead

The Travel Order is issued by the *municipality*, not by the HRMO, so it does
not print `masthead.png`: its head carries no "Human Resource Management
Office" line, no telephone line and no HRMO seal — just the municipal seal, the
blackletter wording, the flourish and the tourism mark. It is drawn from
`config/cs_form.php` plus the seal from `SiteContentService`, which is why
`masthead.png` cannot simply be reused there.

It also closes with a footer the HRMO forms do not have: `pusong-pagsanjan.png`
at the left and the office's contact details on a rule beside it. The mark is
drawn at 146pt wide and **its height follows the file's own proportions**, so
re-cutting the artwork changes how tall it prints rather than squashing it. With
the file absent the contact rule simply spans the full column — a form that is
missing a decoration, never one with a broken-image box on it.

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
