path = 'primeHrMagdalenaLaravel/resources/views/permanent/training/permanentTraining.blade.php'
raw = open(path, 'rb').read()

old = (
    b"            { id: 'trainingCertNo',      val: data.certNo },\r\n"
    b"\r\n"
    b"        ];"
)
new = (
    b"            { id: 'trainingCertNo',      val: data.certNo },\r\n"
    b"            { id: 'trainingRefDoc',      val: data.refDoc },\r\n"
    b"        ];"
)

print('found:', old in raw)
raw = raw.replace(old, new)
open(path, 'wb').write(raw)
print('done')
