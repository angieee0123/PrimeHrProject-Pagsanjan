# DTR form artwork

The branding printed at the head of the **Employee Attendance Logs** (Daily
Time Record) form — the sheet the Detailed DTR modal's *Print Form* and
*Download PDF* buttons produce. Registered in `config/dtr_form.php` under
`brand.wordmark`.

| File | What it is |
|---|---|
| `time-master-wordmark.png` | "TIME MASTER" over "Timekeeping System", as one piece. Lifted from the office's own *Time Master — Employee Attendance Logs* template, so the form prints the real wordmark rather than a rebuilt lookalike. |

The crop is the wordmark's exact bounding box in that template (x 724–1027,
y 42–105 of the 1055 × 1491 source), upsampled 4× and flattened onto pure
white. Two reasons for each of those:

- **The bounding box is exact** because the form places the image by its own
  measured position and width (171.6 × 36.1 pt at the top right). Padding the
  crop would shift the wordmark off the position it holds on the template.
- **Flattened to white, not transparent.** The source is a scan whose "white"
  carries a faint grey cast; left alone it prints as a visible panel behind the
  wordmark on the form's own white. Same rule as `forms/letterhead` — a flat
  background also avoids depending on how a PDF renderer handles alpha.
- **Upsampled** because the source is only ~128 DPI at A4. Upsampling adds no
  detail, but it stops the printer driver doing a nearest-neighbour scale of
  its own, which is what makes a low-resolution logo look jagged rather than
  soft.

## The fallback

If the file is missing, the form **draws** the wordmark instead: `brand.name`
and `brand.tagline` from `config/dtr_form.php`, set in a bold sans at the same
position and size. It is a likeness, not the artwork — the letterforms are a
condensed grotesque this project does not ship.

That path exists so the form still prints on an install where this image was
never copied across, and so the artwork can be swapped for another vendor's
without touching code.

## Consequences worth knowing

- Replacing this file changes the printed form immediately — no code change,
  no restart. Its **height follows the file's own proportions** at a fixed
  171.6 pt width, so a re-cut or a 2× export prints taller instead of squashed.
  Keep the filename, or update `config/dtr_form.php`.
- This is a *timekeeping vendor's* mark, not the municipality's. It is
  deliberately not read from `SiteContentService`: uploading a new municipal
  seal under Admin → Website Content must not replace it, and renaming the
  municipality must not rename it.
