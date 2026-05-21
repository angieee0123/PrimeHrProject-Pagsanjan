/**
 * Multi-format training certificate text parser.
 * Supports labeled metadata, grid layouts, OCR noise, and common PH government / private cert patterns.
 */
(function (global) {
    'use strict';

    const MONTHS = {
        january: '01', february: '02', march: '03', april: '04', may: '05', june: '06',
        july: '07', august: '08', september: '09', october: '10', november: '11', december: '12',
        jan: '01', feb: '02', mar: '03', apr: '04', jun: '06', jul: '07', aug: '08',
        sep: '09', oct: '10', nov: '11', dec: '12',
    };

    const TITLE_NOISE = /^(this is to certify|certificate of|republic of|province of|municipality|government|office of|department of|presented to|awarded to|given to|is hereby|hereby granted|participant|trainee|name|position|division|section|human resource|hrmo|official seal|signature|issued|date|control|serial|ref|no\.?$)/i;

    const GOV_AGENCIES = [
        'Civil Service Commission', 'CSC', 'DILG', 'DICT', 'DepEd', 'DOH', 'DOLE', 'DBM',
        'TESDA', 'CHED', 'GSIS', 'PhilHealth', 'Pag-IBIG', 'BIR', 'DTI', 'DA', 'DENR',
        'Department of Health', 'Department of Education', 'Department of the Interior',
        'National Government', 'Local Government Unit', 'LGU',
    ];

    const LABEL_ALIASES = {
        title: [
            'Training Title', 'Title of Training', 'Title of Seminar', 'Title of Conference',
            'Title of Workshop', 'Program Title', 'Course Title', 'Name of Training',
            'Training Program', 'Seminar Title', 'Workshop Title', 'Subject',
            'Program Entitled', 'Entitled',
        ],
        conductedBy: [
            'Conducted By', 'Organized By', 'Organised By', 'Sponsored By', 'Presented By',
            'Hosted By', 'Facilitated By', 'Training Provider', 'Issuing Organization',
            'Issuing Organisation', 'Resource Speaker', 'In Cooperation With',
            'In Collaboration With', 'Co-organized By',
        ],
        venue: [
            'Venue', 'Location', 'Place', 'Held At', 'Held In', 'Training Venue',
            'Site', 'Conducted At', 'Conducted In',
        ],
        hours: [
            'Number of Hours', 'Training Hours', 'No. of Hours', 'Total Hours',
            'Credit Hours', 'CPD Units', 'CPD Hours', 'Duration', 'Hours Completed',
            'Length of Training',
        ],
        dateFrom: ['Date From', 'Start Date', 'Date Started', 'From', 'Beginning'],
        dateTo: ['Date To', 'End Date', 'Date Ended', 'To', 'Until'],
        inclusiveDates: [
            'Inclusive Dates', 'Inclusive Date', 'Date Conducted', 'Dates Conducted',
            'Training Dates', 'Period', 'Date(s)', 'Duration of Training',
        ],
        certNo: [
            'Certificate No', 'Certificate Number', 'Cert No', 'Cert. No', 'Control No',
            'Control Number', 'Serial No', 'Serial Number', 'Reference No', 'Ref No',
            'Document No', 'ID No',
        ],
        refDoc: [
            'Reference Document No', 'Reference Document Number', 'Reference No',
            'Office Order No', 'Office Order Number', 'Travel Order No', 'Travel Order Number',
            'OO No', 'TO No', 'Authorization No', 'Memorandum Order',
        ],
        issued: ['Issued', 'Issued On', 'Issue Date', 'Date Issued', 'Date of Issue'],
    };

    function normalizeText(raw) {
        if (!raw) return '';
        let t = String(raw)
            .replace(/\r\n/g, '\n')
            .replace(/\u00a0/g, ' ')
            .replace(/[\u2018\u2019\u201a]/g, "'")
            .replace(/[\u201c\u201d\u201e]/g, '"')
            .replace(/\u2013|\u2014/g, '-');
        // Common OCR fixes
        t = t.replace(/(\d)\s+(\d)/g, '$1$2'); // split digits
        t = t.replace(/\bO(\d{3})\b/g, '0$1'); // O24 -> 024 in cert numbers
        return t;
    }

    function toLines(raw) {
        return normalizeText(raw)
            .split(/\n+/)
            .map(l => l.trim())
            .filter(l => l.length > 0);
    }

    function parseAnyDateToISO(str) {
        if (!str) return '';
        const s = String(str).trim().replace(/\.$/, '');
        if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;

        const named = s.match(/([A-Za-z]+)\s+(\d{1,2})(?:st|nd|rd|th)?(?:,?\s*(\d{4}))?/i);
        if (named) {
            const m = MONTHS[named[1].toLowerCase()];
            if (m) {
                const y = named[3] || String(new Date().getFullYear());
                return `${y}-${m}-${String(named[2]).padStart(2, '0')}`;
            }
        }

        const dmy = s.match(/(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})/);
        if (dmy) {
            const a = parseInt(dmy[1], 10);
            const b = parseInt(dmy[2], 10);
            const y = dmy[3];
            // Assume MM/DD or DD/MM — if first > 12, swap
            let month = a;
            let day = b;
            if (a > 12 && b <= 12) { month = b; day = a; }
            return `${y}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        }

        const ymd = s.match(/(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})/);
        if (ymd) {
            return `${ymd[1]}-${ymd[2].padStart(2, '0')}-${ymd[3].padStart(2, '0')}`;
        }

        return '';
    }

    function parseDateRangeString(str) {
        if (!str) return null;
        const cleaned = String(str).trim();

        // March 10-12, 2025 or March 10 – 12, 2025
        const rangeNamed = cleaned.match(
            /([A-Za-z]+)\s+(\d{1,2})\s*[-–]\s*(\d{1,2})(?:,?\s*(\d{4}))?/i
        );
        if (rangeNamed) {
            const month = MONTHS[rangeNamed[1].toLowerCase()];
            if (month) {
                const y = rangeNamed[4] || String(new Date().getFullYear());
                const from = `${y}-${month}-${rangeNamed[2].padStart(2, '0')}`;
                const to = `${y}-${month}-${rangeNamed[3].padStart(2, '0')}`;
                return { from, to };
            }
        }

        const parts = cleaned.split(/\s*[-–—]\s*/);
        if (parts.length >= 2) {
            const from = parseAnyDateToISO(parts[0]);
            const to = parseAnyDateToISO(parts[parts.length - 1]);
            if (from) return { from, to: to || from };
        }

        const single = parseAnyDateToISO(cleaned);
        return single ? { from: single, to: single } : null;
    }

    function cleanValue(val, maxLen) {
        if (!val) return null;
        let v = String(val).trim()
            .replace(/\s+/g, ' ')
            .replace(/^[\s:.\-]+|[\s:.\-]+$/g, '');
        // Stop at next label-like segment
        v = v.replace(/\s{2,}(?:[A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,3})\s*[:.].*$/g, '').trim();
        if (maxLen && v.length > maxLen) v = v.substring(0, maxLen).trim();
        return v || null;
    }

    function extractLabeled(raw, label) {
        const esc = label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const patterns = [
            new RegExp(esc + '\\.?\\s*[:\\-]\\s*([^\\n\\r]{1,250})', 'i'),
            new RegExp(esc + '\\s+([^\\n\\r]{1,250})', 'i'),
        ];
        for (const re of patterns) {
            const m = raw.match(re);
            if (m?.[1]) return cleanValue(m[1], 200);
        }
        return null;
    }

    function extractFromLabelMap(raw, lines) {
        const result = {};
        for (const [key, labels] of Object.entries(LABEL_ALIASES)) {
            for (const label of labels) {
                const val = extractLabeled(raw, label);
                if (val && !result[key]) result[key] = val;
            }
        }

        // Grid layout: label-only line followed by value line
        for (let i = 0; i < lines.length - 1; i++) {
            const line = lines[i];
            const next = lines[i + 1];
            for (const [key, labels] of Object.entries(LABEL_ALIASES)) {
                if (result[key]) continue;
                for (const label of labels) {
                    const re = new RegExp('^' + label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\.?$', 'i');
                    if (re.test(line) && next && !/^[A-Za-z\s]{2,30}:/.test(next)) {
                        result[key] = cleanValue(next, 200);
                        break;
                    }
                }
            }
        }

        // Same-line grid: "Inclusive Dates    March 10-12, 2025"
        for (const line of lines) {
            for (const [key, labels] of Object.entries(LABEL_ALIASES)) {
                if (result[key]) continue;
                for (const label of labels) {
                    const re = new RegExp(
                        '^' + label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*[:\\-]?\\s+(.+)$',
                        'i'
                    );
                    const m = line.match(re);
                    if (m?.[1]) result[key] = cleanValue(m[1], 200);
                }
            }
        }

        return result;
    }

    function extractHours(text, lines) {
        const patterns = [
            /(\d{1,3})\s*(?:credit\s*)?(?:training\s*)?(?:cpd\s*)?hours?/i,
            /(\d{1,3})\s*(?:cpd\s*)?units?/i,
            /(\d{1,3})\s*hrs?\b/i,
            /hours?\s*[:.]?\s*(\d{1,3})/i,
            /duration\s*[:.]?\s*(\d{1,3})\s*(?:hours?|hrs?)/i,
            /(\d{1,3})\s*hour\s+training/i,
            /total\s+of\s+(\d{1,3})\s+training\s+hours?/i,
            /with\s+a\s+total\s+of\s+(\d{1,3})\s+(?:training\s+)?hours?/i,
        ];
        for (const p of patterns) {
            const m = text.match(p);
            const n = parseInt(m?.[1] || '0', 10);
            if (n > 0 && n <= 999) return String(n);
        }
        for (const line of lines) {
            if (/hour|hrs|cpd|unit/i.test(line)) {
                const m = line.match(/(\d{1,3})/);
                if (m && parseInt(m[1], 10) <= 999) return m[1];
            }
        }
        return '';
    }

    function extractTitle(text, lines) {
        const flat = text.replace(/\s+/g, ' ').trim();

        const patterns = [
            /(?:program|seminar|workshop|conference|course|training)\s+entitled\s*[:"']?\s*([^"'\n]{10,220})/i,
            /entitled\s*[:"']?\s*([^"'\n]{10,220})/i,
            /entitled\s+["']?([^"']{10,200})["']?\s+(?:conducted|held|from|with|on)/i,
            /(?:has\s+)?(?:successfully\s+)?(?:completed|attended|participated\s+in)\s+(?:the\s+)?(?:training|seminar|workshop|program)?\s*(?:entitled|on|regarding)?\s*[:"']?\s*([^"'\n]{10,200})/i,
            /(?:training|seminar|workshop|conference)\s+(?:on|about|regarding)\s+[:"']?([^"'\n]{10,200})/i,
            /\u201c([^\u201d\n]{10,220})\u201d/,
            /["']([^"'\n]{10,220})["']/,
        ];

        for (const p of patterns) {
            const m = flat.match(p);
            if (m?.[1]) {
                const c = cleanValue(m[1].replace(/\s*(held|conducted|on|at|from).*/i, ''), 220);
                if (c && c.length >= 10 && !TITLE_NOISE.test(c)) return c;
            }
        }

        // Pick prominent title-like line (long, title case, contains training keywords)
        const candidates = lines.filter(l => {
            if (l.length < 12 || l.length > 180) return false;
            if (TITLE_NOISE.test(l)) return false;
            if (/^\d+$|certificate|republic|province|municipal/i.test(l)) return false;
            return /(seminar|workshop|training|conference|course|program|governance|leadership|management|skills|literacy|ethics|service)/i.test(l)
                || (l === l.toUpperCase() && l.split(/\s+/).length >= 3);
        });
        if (candidates.length) {
            return cleanValue(candidates.sort((a, b) => b.length - a.length)[0], 220);
        }

        return '';
    }

    function extractConductedBy(text, lines) {
        const patterns = [
            /(?:conducted|organized|organised|sponsored|presented|hosted|facilitated)\s+by\s*[:\-]?\s*([^\n]{3,180})/i,
            /(?:in\s+cooperation\s+with|in\s+collaboration\s+with)\s*[:\-]?\s*([^\n]{3,120})/i,
            /(?:under\s+the\s+auspices\s+of)\s*([^\n]{3,120})/i,
            /organized\s+by\s*:\s*([^\n]{3,120})/i,
        ];
        for (const p of patterns) {
            const m = text.match(p);
            if (m?.[1]) return cleanValue(m[1].split(/\s{2,}|Venue|Location|Held|Certificate/i)[0], 150);
        }

        for (const agency of GOV_AGENCIES) {
            const re = new RegExp(agency + '[^\\n,]{0,80}', 'i');
            const m = text.match(re);
            if (m) return cleanValue(m[0], 150);
        }

        const lgu = text.match(
            /(?:Municipal(?:ity)?|City)\s+(?:Government\s+)?of\s+[A-Z][a-zA-Z\s]{2,45}|Provincial\s+Government\s+of\s+[A-Z][a-zA-Z\s]{2,45}/i
        );
        if (lgu) return cleanValue(lgu[0], 150);

        const uni = text.match(
            /(?:University|College|Institute|Academy)\s+of\s+[A-Z][a-zA-Z\s]{2,60}|[A-Z][a-zA-Z\s]{2,40}\s+University/i
        );
        if (uni) return cleanValue(uni[0], 150);

        return '';
    }

    function extractVenue(text) {
        const patterns = [
            /(?:training\s+hours?\s+at|hours?\s+at)\s+([^\n.]{5,120})/i,
            /(?:venue|location|place|held\s+at|held\s+in|conducted\s+at)\s*[:.\-]?\s*([^\n]{4,150})/i,
            /(?:at\s+the\s+)([^\n]{5,120}(?:Hotel|Hall|Center|Centre|Building|Room|Campus|Complex|Auditorium|Convention|Resort|Stadium|Coliseum)[^\n]{0,80})/i,
            /(?:in\s+)([A-Z][a-zA-Z\s,]{4,80}(?:Philippines|Laguna|Manila|Quezon|Cavite|Batangas|Rizal|Bulacan))/i,
        ];
        for (const p of patterns) {
            const m = text.match(p);
            if (m?.[1]) {
                const v = cleanValue(m[1].split(/\s{2,}|Issued|Date|Conducted/i)[0], 150);
                if (v && v.length >= 4) return v;
            }
        }
        return '';
    }

    function extractDates(text, labeled) {
        const result = { dateFrom: '', dateTo: '' };

        if (labeled.inclusiveDates) {
            const p = parseDateRangeString(labeled.inclusiveDates);
            if (p) return { dateFrom: p.from, dateTo: p.to };
        }
        if (labeled.dateFrom) {
            result.dateFrom = parseAnyDateToISO(labeled.dateFrom);
            result.dateTo = labeled.dateTo ? parseAnyDateToISO(labeled.dateTo) : result.dateFrom;
            if (result.dateFrom) return result;
        }

        const flat = text.replace(/\s+/g, ' ');
        const incl = flat.match(
            /(?:inclusive\s*dates?|date\s*conducted|training\s*dates?|period\s+of\s+training)\s*[:.]?\s*([^.;]{5,90})/i
        );
        if (incl) {
            const p = parseDateRangeString(incl[1]);
            if (p) return { dateFrom: p.from, dateTo: p.to };
        }

        const fromTo = flat.match(
            /(?:conducted|held|from)\s+(?:on\s+)?((?:January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[^.,]{0,25})\s+to\s+((?:January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[^.,]{0,25})/i
        );
        if (fromTo) {
            const from = parseAnyDateToISO(fromTo[1]);
            const to = parseAnyDateToISO(fromTo[2]);
            if (from) return { dateFrom: from, dateTo: to || from };
        }

        const dateRe = /(?:January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2}(?:\s*[-–]\s*\d{1,2})?(?:st|nd|rd|th)?,?\s*\d{4}/gi;
        const allNamed = [...flat.matchAll(dateRe)];
        if (allNamed.length) {
            const trainingDates = allNamed.filter(m => {
                const idx = m.index || 0;
                const before = flat.substring(Math.max(0, idx - 30), idx).toLowerCase();
                return !/issued|issue date|birth|expir|valid until/.test(before);
            });
            const use = trainingDates.length ? trainingDates : allNamed;
            const p = parseDateRangeString(use[0][0]);
            if (p) {
                result.dateFrom = p.from;
                result.dateTo = use.length > 1
                    ? (parseDateRangeString(use[use.length - 1][0])?.to || p.to)
                    : p.to;
                return result;
            }
        }

        const numDates = [...flat.matchAll(/(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})/g)];
        if (numDates.length) {
            const first = numDates[0];
            let month = first[1];
            let day = first[2];
            if (parseInt(month, 10) > 12) { month = first[2]; day = first[1]; }
            result.dateFrom = `${first[3]}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
            if (numDates.length > 1) {
                const last = numDates[numDates.length - 1];
                let lm = last[1];
                let ld = last[2];
                if (parseInt(lm, 10) > 12) { lm = last[2]; ld = last[1]; }
                result.dateTo = `${last[3]}-${lm.padStart(2, '0')}-${ld.padStart(2, '0')}`;
            } else {
                result.dateTo = result.dateFrom;
            }
        }

        return result;
    }

    function extractCertAndRef(text, labeled) {
        let certNo = labeled.certNo || '';
        let refDoc = labeled.refDoc || '';

        if (!certNo) {
            const patterns = [
                /(?:certificate|cert\.?)\s*(?:no|number|#)\.?\s*[:.]?\s*([A-Z0-9][A-Z0-9\-\/]{2,40})/i,
                /\b(CERT[\-\/]?\d{4}[\-\/]?\d{2,8})\b/i,
                /\b([A-Z]{2,5}-\d{4}-\d{2,8})\b/,
                /(?:control|serial)\s*(?:no|#)\.?\s*[:.]?\s*([A-Z0-9\-\/]{3,40})/i,
            ];
            for (const p of patterns) {
                const m = text.match(p);
                if (m?.[1]) { certNo = m[1].trim(); break; }
            }
        }

        if (!refDoc) {
            const patterns = [
                /\b((?:OO|TO|MO|SO)-\d{4}-\d{2,8})\b/i,
                /(?:office\s*order|travel\s*order)\s*(?:no\.?|#)?\s*[:.]?\s*([A-Z0-9\-\/]{3,40})/i,
                /(?:reference\s*(?:document|doc)?)\s*(?:no\.?|#)?\s*[:.]?\s*([A-Z0-9\-\/]{3,40})/i,
            ];
            for (const p of patterns) {
                const m = text.match(p);
                if (m?.[1]) { refDoc = m[1].trim(); break; }
            }
        }

        if (!refDoc && certNo) refDoc = certNo;

        return { certNo, refDoc };
    }

    function inferPositionType(title) {
        if (!title) return '';
        const tl = title.toLowerCase();
        if (/leadership|governance|executive|director|strategic|mayor|chief|administrator|manager/.test(tl))
            return 'Managerial';
        if (/supervisor|supervisory|team lead|section head|unit head/.test(tl))
            return 'Supervisory';
        if (/technical|computer|\bit\b|information technology|engineering|records|data|digital|software|network|system|cyber/.test(tl))
            return 'Technical';
        if (/clerical|administrative|secretar|filing|clerk|typist|encoder|office skills/.test(tl))
            return 'Clerical';
        return '';
    }

    function mergeField(primary, fallback) {
        return primary || fallback || '';
    }

    /**
     * Parse raw certificate text into form field values.
     * @param {string} rawText
     * @returns {object} Parsed fields + meta
     */
    function parse(rawText) {
        const raw = normalizeText(rawText);
        const lines = toLines(raw);
        const flat = raw.replace(/\s+/g, ' ').trim();

        const labeled = extractFromLabelMap(raw, lines);
        const dates = extractDates(flat, labeled);
        const certRef = extractCertAndRef(flat, labeled);

        let hours = labeled.hours ? labeled.hours.replace(/[^\d]/g, '') : '';
        if (!hours || parseInt(hours, 10) <= 0) hours = extractHours(flat, lines);

        const title = mergeField(labeled.title, extractTitle(flat, lines));
        const conductedBy = mergeField(labeled.conductedBy, extractConductedBy(flat, lines));
        const venue = mergeField(labeled.venue, extractVenue(flat));

        const result = {
            title: title || '',
            conductedBy: conductedBy || '',
            venue: venue || '',
            hours: hours || '',
            dateFrom: dates.dateFrom || '',
            dateTo: dates.dateTo || '',
            certNo: certRef.certNo || '',
            refDoc: certRef.refDoc || '',
            positionType: inferPositionType(title),
        };

        const fieldKeys = ['title', 'conductedBy', 'venue', 'hours', 'dateFrom', 'dateTo', 'certNo', 'refDoc'];
        const filled = fieldKeys.filter(k => result[k]).length;
        const critical = ['title', 'dateFrom', 'hours'].filter(k => result[k]).length;

        result._meta = {
            filledCount: filled,
            criticalFilled: critical,
            lineCount: lines.length,
            textLength: flat.length,
            lowConfidence: filled < 3 || critical < 2,
        };

        return result;
    }

    global.TrainingCertificateParser = {
        parse,
        parseAnyDateToISO,
        parseDateRangeString,
        normalizeText,
    };
})(typeof window !== 'undefined' ? window : global);
