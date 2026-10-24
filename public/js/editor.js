document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('portfolio-form');
    const codeBox = document.getElementById('html-code');
    const preview = document.getElementById('portfolio-preview');

    if (!form || !codeBox || !preview) return;

    const themeSelect = document.getElementById('theme-select');
    const designSelect = document.getElementById('design-select');
    const storageBase = form.dataset.storageBase || '';
    const fallbackImage = form.dataset.fallbackImage || '';
    const templates = {
        skills: 'skills-template',
        projects: 'projects-template',
        experiences: 'experiences-template',
        education: 'education-template'
    };
    const filePreviewUrls = new WeakMap();

    function addSkillCategoryOption(category) {
        const value = String(category || '').trim();
        if (!value) return;

        const template = document.getElementById('skills-template');
        const selects = Array.from(document.querySelectorAll('#skills-rows [data-item-field="category"]'));
        const templateSelect = template?.content.querySelector('[data-item-field="category"]');
        if (templateSelect) selects.push(templateSelect);

        selects.forEach(function (select) {
            const exists = Array.from(select.options).some(function (option) {
                return option.value.toLowerCase() === value.toLowerCase();
            });
            if (exists) return;

            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.append(option);
        });
    }

    function filePreviewUrl(file) {
        if (!file) return '';
        if (!filePreviewUrls.has(file)) filePreviewUrls.set(file, URL.createObjectURL(file));
        return filePreviewUrls.get(file);
    }

    function compressImage(file) {
        return new Promise(function (resolve) {
            const image = new Image();
            const imageUrl = URL.createObjectURL(file);

            image.onload = function () {
                const maxSize = 1400;
                const scale = Math.min(1, maxSize / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(image.width * scale);
                canvas.height = Math.round(image.height * scale);

                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(imageUrl);

                canvas.toBlob(function (blob) {
                    if (!blob || blob.size >= file.size) {
                        resolve(file);
                        return;
                    }

                    const fileName = file.name.replace(/\.[^.]+$/, '') + '.webp';
                    resolve(new File([blob], fileName, { type: 'image/webp' }));
                }, 'image/webp', 0.75);
            };

            image.onerror = function () {
                URL.revokeObjectURL(imageUrl);
                resolve(file);
            };

            image.src = imageUrl;
        });
    }

    form.addEventListener('submit', async function (event) {
        if (form.dataset.imagesReady === 'true') return;

        const imageInputs = Array.from(form.querySelectorAll('input[type="file"]'));
        const largeImages = imageInputs.filter(function (input) {
            return input.files[0] && input.files[0].size > 250 * 1024;
        });

        if (largeImages.length === 0) return;

        event.preventDefault();
        const submitButton = event.submitter;
        if (submitButton) submitButton.disabled = true;

        for (const input of largeImages) {
            const smallerFile = await compressImage(input.files[0]);
            if (smallerFile === input.files[0]) continue;

            const files = new DataTransfer();
            files.items.add(smallerFile);
            input.files = files.files;
        }

        form.dataset.imagesReady = 'true';
        if (submitButton) submitButton.disabled = false;
        form.requestSubmit(submitButton || undefined);
    });

    const themeCss = {
        light: ':root{--bg:#f5f7fb;--paper:#fff;--text:#172033;--muted:#68758a;--accent:#6655e8;--line:#e7eaf1}*{box-sizing:border-box}body{margin:0;padding:38px 18px;background:var(--bg);color:var(--text);font:16px/1.65 Inter,system-ui,sans-serif}.page{max-width:850px;margin:auto}.hero,.section{background:var(--paper);border:1px solid var(--line);border-radius:22px;padding:34px;margin-bottom:20px}.hero{position:relative;overflow:hidden}.cover{height:118px;margin:-34px -34px 24px;background:linear-gradient(120deg,#e4e2ff,#e5f2ff);background-position:center;background-size:cover}.avatar{width:92px;height:92px;border:5px solid white;border-radius:50%;object-fit:cover;background:#e5e7eb}.avatar-wrap{margin-top:-66px}.eyebrow{font-size:12px;font-weight:700;letter-spacing:.13em;color:var(--accent)}h1{font-size:38px;line-height:1.12;margin:10px 0 4px}h2{font-size:19px;margin:0 0 18px}h3{margin:0 0 5px}p{margin:8px 0;color:var(--muted)}a{color:var(--accent);text-decoration:none}.links,.chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.links a,.chip{border:1px solid var(--line);border-radius:999px;padding:6px 12px;font-size:13px}.skill-list,.timeline,.project-list{display:grid;gap:12px}.skill,.timeline-item,.project{border-top:1px solid var(--line);padding-top:12px}.skill{display:flex;justify-content:space-between;gap:16px}.skill small,.period{color:var(--muted)}.project{display:grid;grid-template-columns:130px 1fr;gap:16px}.project img{width:130px;height:96px;object-fit:cover;border-radius:12px;background:var(--bg)}.project img[src=""]{display:none}.project-tags{font-size:12px;color:var(--muted)}.empty{color:var(--muted)}@media(max-width:600px){body{padding:18px 10px}.hero,.section{padding:22px}.cover{margin:-22px -22px 20px}h1{font-size:30px}.project{grid-template-columns:1fr}.project img{width:100%;height:160px}}',
        dark: ':root{--bg:#0c1220;--paper:#111c2e;--text:#e6edf7;--muted:#9dafc7;--accent:#4ee3c1;--line:#26354b}*{box-sizing:border-box}body{margin:0;padding:38px 18px;background:var(--bg);color:var(--text);font:16px/1.65 Inter,system-ui,sans-serif}.page{max-width:850px;margin:auto}.hero,.section{background:var(--paper);border:1px solid var(--line);border-radius:18px;padding:34px;margin-bottom:20px}.cover{height:118px;margin:-34px -34px 24px;background:linear-gradient(120deg,#123a50,#373174);background-position:center;background-size:cover}.avatar{width:92px;height:92px;border:5px solid var(--paper);border-radius:50%;object-fit:cover;background:#25354a}.avatar-wrap{margin-top:-66px}.eyebrow{font-size:12px;font-weight:700;letter-spacing:.13em;color:var(--accent)}h1{font-size:38px;line-height:1.12;margin:10px 0 4px}h2{font-size:19px;margin:0 0 18px}h3{margin:0 0 5px}p{margin:8px 0;color:var(--muted)}a{color:var(--accent);text-decoration:none}.links,.chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.links a,.chip{border:1px solid var(--line);border-radius:999px;padding:6px 12px;font-size:13px}.skill-list,.timeline,.project-list{display:grid;gap:12px}.skill,.timeline-item,.project{border-top:1px solid var(--line);padding-top:12px}.skill{display:flex;justify-content:space-between;gap:16px}.skill small,.period{color:var(--muted)}.project{display:grid;grid-template-columns:130px 1fr;gap:16px}.project img{width:130px;height:96px;object-fit:cover;border-radius:12px;background:var(--bg)}.project img[src=""]{display:none}.project-tags{font-size:12px;color:var(--muted)}.empty{color:var(--muted)}@media(max-width:600px){body{padding:18px 10px}.hero,.section{padding:22px}.cover{margin:-22px -22px 20px}h1{font-size:30px}.project{grid-template-columns:1fr}.project img{width:100%;height:160px}}',
        glass: ':root{--bg:#dfe8fb;--paper:rgba(255,255,255,.67);--text:#24263f;--muted:#666b86;--accent:#6757d9;--line:rgba(255,255,255,.8)}*{box-sizing:border-box}body{margin:0;padding:38px 18px;background:radial-gradient(circle at 10% 10%,#f3eaff,#dceeff 48%,#e6e0ff);color:var(--text);font:16px/1.65 Inter,system-ui,sans-serif}.page{max-width:850px;margin:auto}.hero,.section{background:var(--paper);border:1px solid var(--line);border-radius:24px;padding:34px;margin-bottom:20px;backdrop-filter:blur(12px);box-shadow:0 18px 60px rgba(71,73,135,.12)}.cover{height:118px;margin:-34px -34px 24px;background:linear-gradient(120deg,rgba(186,175,255,.8),rgba(158,222,255,.8));background-position:center;background-size:cover}.avatar{width:92px;height:92px;border:5px solid rgba(255,255,255,.9);border-radius:50%;object-fit:cover;background:#eee}.avatar-wrap{margin-top:-66px}.eyebrow{font-size:12px;font-weight:700;letter-spacing:.13em;color:var(--accent)}h1{font-size:38px;line-height:1.12;margin:10px 0 4px}h2{font-size:19px;margin:0 0 18px}h3{margin:0 0 5px}p{margin:8px 0;color:var(--muted)}a{color:var(--accent);text-decoration:none}.links,.chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.links a,.chip{border:1px solid var(--line);border-radius:999px;padding:6px 12px;font-size:13px}.skill-list,.timeline,.project-list{display:grid;gap:12px}.skill,.timeline-item,.project{border-top:1px solid var(--line);padding-top:12px}.skill{display:flex;justify-content:space-between;gap:16px}.skill small,.period{color:var(--muted)}.project{display:grid;grid-template-columns:130px 1fr;gap:16px}.project img{width:130px;height:96px;object-fit:cover;border-radius:12px;background:var(--bg)}.project img[src=""]{display:none}.project-tags{font-size:12px;color:var(--muted)}.empty{color:var(--muted)}@media(max-width:600px){body{padding:18px 10px}.hero,.section{padding:22px}.cover{margin:-22px -22px 20px}h1{font-size:30px}.project{grid-template-columns:1fr}.project img{width:100%;height:160px}}',
        terminal: ':root{--bg:#050907;--paper:#08130d;--text:#d6ffe3;--muted:#8ca794;--accent:#4ade80;--line:#21462e}*{box-sizing:border-box}body{margin:0;padding:38px 18px;background:var(--bg);color:var(--text);font:15px/1.7 "SFMono-Regular",Consolas,monospace}.page{max-width:850px;margin:auto}.hero,.section{background:var(--paper);border:1px solid var(--line);padding:30px;margin-bottom:16px}.cover{height:70px;margin:-30px -30px 24px;background:repeating-linear-gradient(135deg,#082010,#082010 12px,#0d2817 12px,#0d2817 24px);background-position:center;background-size:cover}.avatar{width:76px;height:76px;border:2px solid var(--accent);border-radius:50%;object-fit:cover;background:#10291b}.avatar-wrap{margin-top:-50px}.eyebrow{font-size:11px;font-weight:700;letter-spacing:.1em;color:var(--accent)}h1{font-size:32px;line-height:1.2;margin:10px 0 4px}h2{font-size:17px;margin:0 0 18px;color:var(--accent)}h3{margin:0 0 5px}p{margin:8px 0;color:var(--muted)}a{color:var(--accent)}.links,.chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.links a,.chip{border:1px solid var(--line);padding:4px 10px;font-size:12px}.skill-list,.timeline,.project-list{display:grid;gap:12px}.skill,.timeline-item,.project{border-top:1px dashed var(--line);padding-top:12px}.skill{display:flex;justify-content:space-between;gap:16px}.skill small,.period{color:var(--muted)}.project{display:grid;grid-template-columns:130px 1fr;gap:16px}.project img{width:130px;height:96px;object-fit:cover;border:1px solid var(--line);background:var(--bg)}.project img[src=""]{display:none}.project-tags{font-size:12px;color:var(--muted)}.empty{color:var(--muted)}@media(max-width:600px){body{padding:18px 10px}.hero,.section{padding:22px}.cover{margin:-22px -22px 20px}h1{font-size:27px}.project{grid-template-columns:1fr}.project img{width:100%;height:160px}}',
        corporate: ':root{--bg:#f3f6fb;--paper:#fff;--text:#1b2c4b;--muted:#64738b;--accent:#1e3a8a;--line:#dfe6f0}*{box-sizing:border-box}body{margin:0;padding:38px 18px;background:var(--bg);color:var(--text);font:16px/1.6 Arial,Helvetica,sans-serif}.page{max-width:850px;margin:auto}.hero,.section{background:var(--paper);border:1px solid var(--line);padding:30px 34px;margin-bottom:18px}.hero{border-top:7px solid var(--accent)}.cover{height:88px;margin:-23px -34px 24px;background:#e8eef8;background-position:center;background-size:cover}.avatar{width:84px;height:84px;border:3px solid white;border-radius:50%;object-fit:cover;background:#e5e7eb}.avatar-wrap{margin-top:-56px}.eyebrow{font-size:12px;font-weight:700;letter-spacing:.12em;color:var(--accent)}h1{font-size:36px;line-height:1.2;margin:10px 0 4px}h2{font-size:18px;margin:0 0 14px;padding-bottom:8px;border-bottom:2px solid var(--line);text-transform:uppercase;letter-spacing:.06em}h3{margin:0 0 5px}p{margin:8px 0;color:var(--muted)}a{color:var(--accent);text-decoration:none}.links,.chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.links a,.chip{border:1px solid var(--line);padding:5px 10px;font-size:13px}.skill-list,.timeline,.project-list{display:grid;gap:10px}.skill,.timeline-item,.project{border-top:1px solid var(--line);padding-top:10px}.skill{display:flex;justify-content:space-between;gap:16px}.skill small,.period{color:var(--muted)}.project{display:grid;grid-template-columns:130px 1fr;gap:16px}.project img{width:130px;height:96px;object-fit:cover;background:var(--bg)}.project img[src=""]{display:none}.project-tags{font-size:12px;color:var(--muted)}.empty{color:var(--muted)}@media print{body{background:#fff;padding:0}.hero,.section{box-shadow:none;border-color:#ddd;break-inside:avoid}}@media(max-width:600px){body{padding:16px 10px}.hero,.section{padding:22px}.cover{margin:-15px -22px 20px}h1{font-size:30px}.project{grid-template-columns:1fr}.project img{width:100%;height:160px}}'
    };

    const designCss = {
        studio: `
            body { padding: 28px; }
            main.page[data-design="studio"] { max-width: 1040px; }
            main.page[data-design="studio"] .hero { min-height: 390px; display: flex; flex-direction: column; justify-content: flex-end; padding: 48px; position: relative; }
            main.page[data-design="studio"] .cover { position: absolute; inset: 0; height: auto; margin: 0; opacity: .16; }
            main.page[data-design="studio"] .avatar-wrap, main.page[data-design="studio"] .hero > :not(.cover) { position: relative; }
            main.page[data-design="studio"] .avatar-wrap { margin: 0 0 12px; }
            main.page[data-design="studio"] .section { padding: 30px 36px; }
            @media (max-width: 600px) { body { padding: 12px; } main.page[data-design="studio"] .hero { min-height: 320px; padding: 26px; } }
        `,
        sidebar: `
            body { padding: 24px; }
            main.page[data-design="sidebar"] { max-width: 1120px; display: grid; grid-template-columns: minmax(240px, .75fr) minmax(0, 1.5fr); gap: 18px; align-items: start; }
            main.page[data-design="sidebar"] .hero { grid-column: 1; grid-row: 1 / span 4; position: sticky; top: 18px; }
            main.page[data-design="sidebar"] .cover { height: 160px; }
            main.page[data-design="sidebar"] .section { grid-column: 2; margin-bottom: 0; }
            @media (max-width: 720px) { main.page[data-design="sidebar"] { display: block; } main.page[data-design="sidebar"] .hero { position: static; margin-bottom: 14px; } }
        `,
        showcase: `
            body { padding: 24px; }
            main.page[data-design="showcase"] { max-width: 1160px; }
            main.page[data-design="showcase"] .hero { min-height: 320px; display: flex; flex-direction: column; justify-content: center; padding: 54px; position: relative; overflow: hidden; }
            main.page[data-design="showcase"] .cover { position: absolute; inset: 0; height: auto; margin: 0; opacity: .18; }
            main.page[data-design="showcase"] .hero > :not(.cover) { position: relative; }
            main.page[data-design="showcase"] .avatar-wrap { margin: 0 0 12px; }
            main.page[data-design="showcase"] .project-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
            main.page[data-design="showcase"] .project { display: block; padding: 0; overflow: hidden; border: 1px solid var(--line); border-radius: 16px; }
            main.page[data-design="showcase"] .project > div { padding: 18px; }
            main.page[data-design="showcase"] .project img { width: 100%; height: 210px; display: block; border-radius: 0; }
            @media (max-width: 650px) { body { padding: 12px; } main.page[data-design="showcase"] .hero { padding: 28px; } main.page[data-design="showcase"] .project-list { grid-template-columns: 1fr; } }
        `,
        editorial: `
            body { padding: 46px 24px; font-family: Georgia, 'Times New Roman', serif; }
            main.page[data-design="editorial"] { max-width: 900px; }
            main.page[data-design="editorial"] .hero { padding: 58px 12px 42px; background: transparent; border: 0; border-bottom: 1px solid var(--line); border-radius: 0; }
            main.page[data-design="editorial"] .cover { height: 190px; margin: 0 0 22px; border-radius: 4px; }
            main.page[data-design="editorial"] .avatar-wrap { margin: 0 0 14px; }
            main.page[data-design="editorial"] h1 { font-family: Georgia, 'Times New Roman', serif; font-size: clamp(42px, 7vw, 70px); letter-spacing: -2px; }
            main.page[data-design="editorial"] .section { padding: 30px 12px; background: transparent; border: 0; border-bottom: 1px solid var(--line); border-radius: 0; }
            main.page[data-design="editorial"] h2 { font-size: 15px; letter-spacing: .14em; text-transform: uppercase; }
            main.page[data-design="editorial"] .project-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            @media (max-width: 620px) { body { padding: 20px 12px; } main.page[data-design="editorial"] .project-list { grid-template-columns: 1fr; } }
        `,
        minimal: `
            body { padding: 56px 24px; }
            main.page[data-design="minimal"] { max-width: 780px; }
            main.page[data-design="minimal"] .hero { padding: 30px 0 52px; background: transparent; border: 0; border-radius: 0; text-align: center; }
            main.page[data-design="minimal"] .cover { display: none; }
            main.page[data-design="minimal"] .avatar-wrap { margin: 0 0 20px; }
            main.page[data-design="minimal"] .hero .links { justify-content: center; }
            main.page[data-design="minimal"] .section { padding: 27px 0; background: transparent; border: 0; border-top: 1px solid var(--line); border-radius: 0; }
            main.page[data-design="minimal"] .project { grid-template-columns: 100px 1fr; }
            main.page[data-design="minimal"] .project img { width: 100px; height: 84px; }
            @media (max-width: 600px) { body { padding: 24px 14px; } main.page[data-design="minimal"] .project { grid-template-columns: 1fr; } main.page[data-design="minimal"] .project img { width: 100%; height: 180px; } }
        `,
        terminal: `
            body { padding: 24px; font-family: 'SFMono-Regular', Consolas, monospace; }
            main.page[data-design="terminal"] { max-width: 960px; }
            main.page[data-design="terminal"] .hero, main.page[data-design="terminal"] .section { border-radius: 3px; box-shadow: none; }
            main.page[data-design="terminal"] .hero { border-left: 5px solid var(--accent); }
            main.page[data-design="terminal"] .cover { height: 72px; }
            main.page[data-design="terminal"] .eyebrow::before { content: '$ '; }
            main.page[data-design="terminal"] h2::before { content: '> '; color: var(--accent); }
            main.page[data-design="terminal"] .project { border-radius: 3px; }
            main.page[data-design="terminal"] .links a { text-decoration: underline; }
            @media (max-width: 600px) { body { padding: 12px; } }
        `
    };

    const unboxedDesignCss = `
        main.page[data-design] > .hero,
        main.page[data-design] > .section {
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        main.page[data-design] .project {
            background: transparent !important;
            border: 0 !important;
            border-top: 1px solid var(--line) !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        main.page[data-design] .project img { border-radius: 0 !important; }

        main.page[data-design="studio"] > .hero {
            border-bottom: 1px solid var(--line) !important;
            padding-bottom: 38px;
        }
        main.page[data-design="studio"] > .section {
            border-bottom: 1px solid var(--line) !important;
            padding-left: 0;
            padding-right: 0;
        }

        main.page[data-design="sidebar"] > .hero {
            border-right: 1px solid var(--line) !important;
            padding-right: 28px;
        }
        main.page[data-design="sidebar"] > .section {
            border-bottom: 1px solid var(--line) !important;
            padding-left: 0;
            padding-right: 0;
        }

        main.page[data-design="showcase"] > .hero {
            border-bottom: 1px solid var(--line) !important;
            padding-bottom: 36px;
        }
        main.page[data-design="showcase"] > .section {
            border-bottom: 1px solid var(--line) !important;
            padding-left: 0;
            padding-right: 0;
        }
        main.page[data-design="showcase"] .project > div { padding: 14px 0 0; }

        main.page[data-design="editorial"] > .hero {
            border-bottom: 1px solid var(--line) !important;
        }
        main.page[data-design="editorial"] > .section {
            border-bottom: 1px solid var(--line) !important;
            padding-left: 0;
            padding-right: 0;
        }

        main.page[data-design="minimal"] > .hero {
            border-bottom: 1px solid var(--line) !important;
        }
        main.page[data-design="minimal"] > .section {
            border-top: 1px solid var(--line) !important;
        }

        main.page[data-design="terminal"] > .hero {
            border-left: 4px solid var(--accent) !important;
            border-bottom: 1px dashed var(--line) !important;
            padding-left: 22px;
        }
        main.page[data-design="terminal"] > .section {
            border-bottom: 1px dashed var(--line) !important;
            padding-left: 0;
            padding-right: 0;
        }

        main.page .skill-list { gap: 18px; }
        main.page .skill-group { display: grid; grid-template-columns: minmax(125px, .32fr) 1fr; align-items: start; gap: 18px; padding: 0 0 18px; border-bottom: 1px solid var(--line); }
        main.page .skill-group:last-child { padding-bottom: 0; border-bottom: 0; }
        main.page .skill-group-title { margin: 5px 0 0; color: var(--text); font-size: 12px; font-weight: 800; letter-spacing: .08em; line-height: 1.5; text-transform: uppercase; }
        main.page .skill-chips { display: flex; flex-wrap: wrap; gap: 9px; }
        main.page .skill-chip { display: inline-flex; align-items: center; gap: 11px; padding: 8px 11px; border: 1px solid var(--line); border-radius: 9px; background: var(--paper); }
        main.page .skill-chip b { font-size: 13px; }
        main.page .skill-chip small { color: var(--muted); font-size: 10px; }
        @media (max-width: 600px) {
            main.page .skill-group { grid-template-columns: 1fr; gap: 9px; }
        }
    `;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character];
        });
    }

    function readRows(type) {
        const container = document.getElementById(type + '-rows');
        if (!container) return [];

        return Array.from(container.querySelectorAll('[data-row]')).map(function (row) {
            const values = {};
            row.querySelectorAll('[data-item-field]').forEach(function (input) {
                const key = input.dataset.itemField;
                values[key] = input.type === 'checkbox' ? input.checked : input.value;
            });
            return values;
        });
    }

    function getField(name) {
        const input = form.querySelector('[data-preview-field="' + name + '"]');
        return input ? input.value.trim() : '';
    }

    function generateHtml() {
        const theme = themeSelect.value;
        const design = designSelect.value;
        const skills = readRows('skills').filter(function (row) { return row.name; });
        const projects = readRows('projects').filter(function (row) { return row.title; });
        const experiences = readRows('experiences').filter(function (row) { return row.company || row.role; });
        const education = readRows('education').filter(function (row) { return row.institution || row.degree; });
        const name = getField('full_name') || 'Your Name';
        const headline = getField('headline') || 'Developer';
        const skillGroups = new Map();

        skills.forEach(function (skill) {
            const category = String(skill.category || '').trim() || 'Other';
            if (!skillGroups.has(category)) skillGroups.set(category, []);
            skillGroups.get(category).push(skill);
        });

        const skillContent = Array.from(skillGroups.entries()).map(function ([category, items]) {
            const chips = items.map(function (item) {
                return '<span class="skill-chip" data-skill data-name="' + escapeHtml(item.name) + '" data-category="' + escapeHtml(item.category) + '" data-proficiency="' + escapeHtml(item.proficiency) + '"><b data-item-name>' + escapeHtml(item.name) + '</b><small data-item-proficiency>' + escapeHtml(item.proficiency) + '%</small></span>';
            }).join('');

            return '<div class="skill-group"><h3 class="skill-group-title">' + escapeHtml(category) + '</h3><div class="skill-chips">' + chips + '</div></div>';
        }).join('');

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title data-field="portfolio_title">' + escapeHtml(getField('portfolio_title') || 'My portfolio') + '</title><style>' + (themeCss[theme] || themeCss.light) + (designCss[design] || designCss.studio) + unboxedDesignCss + '</style></head><body><main class="page" data-theme="' + theme + '" data-design="' + design + '">' +
            '<header class="hero"><div class="cover" data-cover style="background-image:url(\'%%COVER%%\')"></div><div class="avatar-wrap"><img class="avatar" src="%%AVATAR%%" alt="Profile photo"></div><span class="eyebrow" data-field="headline">' + escapeHtml(headline) + '</span><h1 data-field="full_name">' + escapeHtml(name) + '</h1><p data-field="bio">' + escapeHtml(getField('bio') || 'Add a short introduction in the editor.') + '</p><div class="links">' +
            (getField('email') ? '<a data-field="email" data-value="' + escapeHtml(getField('email')) + '" href="mailto:' + escapeHtml(getField('email')) + '">' + escapeHtml(getField('email')) + '</a>' : '') +
            (getField('location') ? '<span data-field="location">' + escapeHtml(getField('location')) + '</span>' : '') +
            (getField('phone') ? '<span data-field="phone">' + escapeHtml(getField('phone')) + '</span>' : '') +
            ['github_url', 'linkedin_url', 'website_url'].map(function (key) { return getField(key) ? '<a data-field="' + key + '" data-value="' + escapeHtml(getField(key)) + '" href="' + escapeHtml(getField(key)) + '">' + key.replace('_url', '').replace('_', ' ') + ' ↗</a>' : ''; }).join('') +
            '</div></header>' +
            section('Skills', 'skills', skillContent) +
            section('Selected projects', 'projects', projects.map(projectCard).join('')) +
            section('Experience', 'experiences', experiences.map(function (item) { return '<article class="timeline-item" data-experience data-role="' + escapeHtml(item.role) + '" data-company="' + escapeHtml(item.company) + '" data-description="' + escapeHtml(item.description) + '" data-start-date="' + escapeHtml(item.start_date) + '" data-end-date="' + escapeHtml(item.end_date) + '" data-current-job="' + (item.current_job ? '1' : '0') + '"><h3 data-item-role>' + escapeHtml(item.role || 'Role') + '</h3><span data-item-company>' + escapeHtml(item.company) + '</span><p data-item-period>' + escapeHtml(item.start_date || '') + (item.current_job ? ' — Present' : (item.end_date ? ' — ' + escapeHtml(item.end_date) : '')) + '</p><p data-item-description>' + escapeHtml(item.description) + '</p></article>'; }).join('')) +
            section('Education', 'education', education.map(function (item) { return '<article class="timeline-item" data-education data-institution="' + escapeHtml(item.institution) + '" data-degree="' + escapeHtml(item.degree) + '" data-study-field="' + escapeHtml(item.field) + '" data-description="' + escapeHtml(item.description) + '" data-start-date="' + escapeHtml(item.start_date) + '" data-end-date="' + escapeHtml(item.end_date) + '"><h3 data-item-degree>' + escapeHtml(item.degree || 'Degree or course') + '</h3><span data-item-institution>' + escapeHtml(item.institution) + '</span><span data-item-study-field>' + escapeHtml(item.field) + '</span><p data-item-period>' + escapeHtml(item.start_date || '') + (item.end_date ? ' — ' + escapeHtml(item.end_date) : '') + '</p><p data-item-description>' + escapeHtml(item.description) + '</p></article>'; }).join('')) +
            '</main></body></html>';

        function section(title, key, content) {
            return '<section class="section"><h2>' + title + '</h2><div data-list="' + key + '" class="' + (key === 'projects' ? 'project-list' : (key === 'skills' ? 'skill-list' : 'timeline')) + '">' + (content || '<p class="empty">Add details in the editor to fill this section.</p>') + '</div></section>';
        }

        function projectCard(item, index) {
            const tags = (item.tags || '').split(',').map(function (tag) { return tag.trim(); }).filter(Boolean);
            return '<article class="project" data-project data-id="' + escapeHtml(item.id || '') + '" data-title="' + escapeHtml(item.title) + '" data-description="' + escapeHtml(item.description) + '" data-tags="' + escapeHtml(item.tags) + '" data-demo-url="' + escapeHtml(item.demo_url) + '" data-github-url="' + escapeHtml(item.github_url) + '" data-image-index="' + index + '"><img src="%%PROJECT_' + index + '%%" alt="' + escapeHtml(item.title) + ' screenshot"><div><h3 data-item-title>' + escapeHtml(item.title) + '</h3><p data-item-description>' + escapeHtml(item.description) + '</p><p class="project-tags" data-item-tags>' + tags.map(escapeHtml).join(' · ') + '</p><div class="links">' + (item.demo_url ? '<a data-project-demo href="' + escapeHtml(item.demo_url) + '">Live demo ↗</a>' : '') + (item.github_url ? '<a data-project-github href="' + escapeHtml(item.github_url) + '">Source code ↗</a>' : '') + '</div></div></article>';
        }
    }

    function previewHtml(html) {
        const avatarFile = document.getElementById('avatar-file').files[0];
        const coverFile = document.getElementById('cover-file').files[0];
        const avatarPath = document.getElementById('avatar-current').value;
        const coverPath = document.getElementById('cover-current').value;
        const avatarUrl = avatarFile ? filePreviewUrl(avatarFile) : (avatarPath ? storageBase + avatarPath : '');
        const coverUrl = coverFile ? filePreviewUrl(coverFile) : (coverPath ? storageBase + coverPath : '');
        let result = html.replaceAll('%%AVATAR%%', escapeHtml(avatarUrl)).replaceAll('%%COVER%%', escapeHtml(coverUrl));

        readRows('projects').forEach(function (project, index) {
            const row = document.querySelectorAll('#projects-rows [data-row]')[index];
            const file = row?.querySelector('input[type="file"]')?.files[0];
            const path = row?.querySelector('.current-image-path')?.value;
            const imageUrl = file ? filePreviewUrl(file) : (path ? storageBase + path : fallbackImage);
            result = result.replaceAll('%%PROJECT_' + index + '%%', escapeHtml(imageUrl));
        });

        return result;
    }

    function updatePreview() {
        preview.srcdoc = previewHtml(codeBox.value);
    }

    function setFieldFromCode(doc, name) {
        const input = form.querySelector('[data-preview-field="' + name + '"]');
        const node = doc.querySelector('[data-field="' + name + '"]');
        if (!input || !node) return;

        let value = node.textContent.trim();
        const href = node.getAttribute('href');
        if (node.tagName === 'A' && href) value = href;
        if (name === 'email' && href?.startsWith('mailto:')) value = href.slice(7);
        input.value = value;
    }

    function syncRows(type, items) {
        const container = document.getElementById(type + '-rows');
        if (!container) return;
        const oldRows = Array.from(container.querySelectorAll('[data-row]'));
        const unusedRows = oldRows.slice();
        const rows = items.map(function (item, index) {
            let row = null;
            if (type === 'projects' && item.id) {
                row = oldRows.find(function (oldRow) {
                    return oldRow.querySelector('[data-item-field="id"]')?.value === String(item.id);
                });
            }
            if (!row) row = unusedRows.find(function (oldRow) { return !oldRows[index] || oldRow === oldRows[index]; });
            if (!row) {
                const fragment = document.getElementById(templates[type]).content.cloneNode(true);
                row = fragment.querySelector('[data-row]');
            }
            const oldIndex = unusedRows.indexOf(row);
            if (oldIndex >= 0) unusedRows.splice(oldIndex, 1);

            row.querySelectorAll('[data-item-field]').forEach(function (input) {
                const key = input.dataset.itemField;
                if (key === 'id' || key === 'image') return;
                if (type === 'skills' && key === 'category') addSkillCategoryOption(item[key]);
                if (key === 'current_job') input.checked = item[key] === true || item[key] === '1';
                else if (item[key] !== undefined && input.value !== String(item[key] ?? '')) input.value = item[key] ?? '';
            });

            return row;
        });

        container.replaceChildren(...rows);
        reindexRows(type);
    }

    function syncFormFromCode() {
        const doc = new DOMParser().parseFromString(codeBox.value, 'text/html');
        ['portfolio_title', 'full_name', 'headline', 'bio', 'email', 'phone', 'location', 'github_url', 'linkedin_url', 'website_url'].forEach(function (name) {
            setFieldFromCode(doc, name);
        });

        const main = doc.querySelector('[data-theme]');
        if (main && themeCss[main.dataset.theme]) themeSelect.value = main.dataset.theme;
        if (main && main.dataset.design && designCss[main.dataset.design]) designSelect.value = main.dataset.design;

        const skills = Array.from(doc.querySelectorAll('[data-skill]')).map(function (node) {
            const name = node.querySelector('[data-item-name]');
            const category = node.querySelector('[data-item-category]');
            const proficiency = node.querySelector('[data-item-proficiency]');
            return {
                name: name ? name.textContent.trim() : (node.dataset.name || ''),
                category: category ? category.textContent.trim() : (node.dataset.category || ''),
                proficiency: proficiency ? proficiency.textContent.replace('%', '').trim() : (node.dataset.proficiency || 50)
            };
        });
        const projects = Array.from(doc.querySelectorAll('[data-project]')).map(function (node) {
            const title = node.querySelector('[data-item-title]');
            const description = node.querySelector('[data-item-description]');
            const tags = node.querySelector('[data-item-tags]');
            const demo = node.querySelector('[data-project-demo]');
            const github = node.querySelector('[data-project-github]');
            return {
                id: node.dataset.id || '',
                title: title ? title.textContent.trim() : (node.dataset.title || ''),
                description: description ? description.textContent.trim() : (node.dataset.description || ''),
                tags: tags ? tags.textContent.split('·').map(function (tag) { return tag.trim(); }).filter(Boolean).join(', ') : (node.dataset.tags || ''),
                demo_url: demo ? demo.getAttribute('href') : (node.dataset.demoUrl || ''),
                github_url: github ? github.getAttribute('href') : (node.dataset.githubUrl || '')
            };
        });
        const experiences = Array.from(doc.querySelectorAll('[data-experience]')).map(function (node) {
            const role = node.querySelector('[data-item-role]');
            const company = node.querySelector('[data-item-company]');
            const description = node.querySelector('[data-item-description]');
            const period = node.querySelector('[data-item-period]')?.textContent.trim() || '';
            const dates = period.split('—').map(function (date) { return date.trim(); });
            return {
                role: role ? role.textContent.trim() : (node.dataset.role || ''),
                company: company ? company.textContent.trim() : (node.dataset.company || ''),
                description: description ? description.textContent.trim() : (node.dataset.description || ''),
                start_date: dates[0] || node.dataset.startDate || '',
                end_date: dates[1] === 'Present' ? '' : (dates[1] || node.dataset.endDate || ''),
                current_job: dates.length > 1 ? (dates[1] === 'Present' ? '1' : '0') : (node.dataset.currentJob || '0')
            };
        });
        const education = Array.from(doc.querySelectorAll('[data-education]')).map(function (node) {
            const degree = node.querySelector('[data-item-degree]');
            const institution = node.querySelector('[data-item-institution]');
            const studyField = node.querySelector('[data-item-study-field]');
            const description = node.querySelector('[data-item-description]');
            const period = node.querySelector('[data-item-period]')?.textContent.trim() || '';
            const dates = period.split('—').map(function (date) { return date.trim(); });
            return {
                institution: institution ? institution.textContent.trim() : (node.dataset.institution || ''),
                degree: degree ? degree.textContent.trim() : (node.dataset.degree || ''),
                field: studyField ? studyField.textContent.trim() : (node.dataset.studyField || ''),
                description: description ? description.textContent.trim() : (node.dataset.description || ''),
                start_date: dates[0] || node.dataset.startDate || '',
                end_date: dates[1] || node.dataset.endDate || ''
            };
        });

        if (doc.querySelector('[data-list="skills"]')) syncRows('skills', skills);
        if (doc.querySelector('[data-list="projects"]')) syncRows('projects', projects);
        if (doc.querySelector('[data-list="experiences"]')) syncRows('experiences', experiences);
        if (doc.querySelector('[data-list="education"]')) syncRows('education', education);
    }

    function updateCodeFromForm(updateStyle) {
        const doc = new DOMParser().parseFromString(codeBox.value, 'text/html');
        let main = doc.querySelector('[data-theme]');
        if (!main) {
            codeBox.value = generateHtml();
            return;
        }

        const previousTheme = main.dataset.theme;
        const previousDesign = main.dataset.design;
        main.dataset.theme = themeSelect.value;
        main.dataset.design = designSelect.value;
        if (updateStyle || !previousTheme || !previousDesign) {
            const style = doc.querySelector('style');
            if (style) {
                style.textContent = (themeCss[themeSelect.value] || themeCss.light)
                    + (designCss[designSelect.value] || designCss.studio)
                    + unboxedDesignCss;
            }
        }

        form.querySelectorAll('[data-preview-field]').forEach(function (input) {
            const node = doc.querySelector('[data-field="' + input.dataset.previewField + '"]');
            if (!node) return;
            if (node.tagName === 'A') {
                node.dataset.value = input.value;
                if (input.dataset.previewField === 'email') node.href = 'mailto:' + input.value;
                else node.href = input.value;
            } else {
                node.textContent = input.value;
            }
        });

        const lists = {
            skills: readRows('skills').filter(function (row) { return row.name; }),
            projects: readRows('projects').filter(function (row) { return row.title; }),
            experiences: readRows('experiences').filter(function (row) { return row.company || row.role; }),
            education: readRows('education').filter(function (row) { return row.institution || row.degree; })
        };

        Object.keys(lists).forEach(function (type) {
            const container = doc.querySelector('[data-list="' + type + '"]');
            if (!container) return;
            const generated = new DOMParser().parseFromString(generateHtml(), 'text/html');
            const generatedContainer = generated.querySelector('[data-list="' + type + '"]');
            if (generatedContainer) container.innerHTML = generatedContainer.innerHTML;
            else container.innerHTML = '';
        });

        const titleNode = doc.querySelector('[data-field="portfolio_title"]');
        if (titleNode) titleNode.textContent = document.getElementById('portfolio-title').value;
        codeBox.value = '<!doctype html>\n' + doc.documentElement.outerHTML;
    }

    function addRow(type) {
        const container = document.getElementById(type + '-rows');
        const template = document.getElementById(templates[type]);
        if (!container || !template) return;
        const index = container.querySelectorAll('[data-row]').length;
        const fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[name]').forEach(function (input) {
            input.name = input.name.replaceAll('__INDEX__', String(index));
        });
        container.appendChild(fragment);
        reindexRows(type);
        updateCodeFromForm(false);
        updatePreview();
    }

    function reindexRows(type) {
        const container = document.getElementById(type + '-rows');
        if (!container) return;
        container.querySelectorAll('[data-row]').forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace(/\[\d+\]/g, '[' + index + ']').replaceAll('__INDEX__', String(index));
            });
        });
    }

    document.querySelectorAll('[data-add-row]').forEach(function (button) {
        button.addEventListener('click', function () { addRow(button.dataset.addRow); });
    });

    document.getElementById('add-skill-category')?.addEventListener('click', function () {
        const input = document.getElementById('new-skill-category');
        const status = document.getElementById('skill-category-status');
        const category = input.value.trim();

        if (!category) {
            status.textContent = 'Enter a category name first.';
            input.focus();
            return;
        }

        addSkillCategoryOption(category);
        status.textContent = 'Category added. Choose it from a skill category list.';
        input.value = '';
    });

    form.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-row');
        if (removeButton) {
            const row = removeButton.closest('[data-row]');
            const type = row?.dataset.row;
            row?.remove();
            if (type) reindexRows(type);
            updateCodeFromForm(false);
            updatePreview();
        }
    });

    form.addEventListener('input', function (event) {
        if (event.target.matches('input[type="file"]')) {
            const file = event.target.files[0];
            const previewImage = event.target.closest('.field')?.querySelector('.upload-preview');
            if (file && previewImage) {
                previewImage.src = filePreviewUrl(file);
                previewImage.hidden = false;
            }
        }
        updateCodeFromForm(false);
        updatePreview();
    });

    form.addEventListener('change', function (event) {
        if (!event.target.matches('input[type="file"]')) {
            updateCodeFromForm(false);
            updatePreview();
            return;
        }
        const file = event.target.files[0];
        const previewImage = event.target.closest('.field')?.querySelector('.upload-preview');
        if (file && previewImage) {
            previewImage.src = filePreviewUrl(file);
            previewImage.hidden = false;
        }
        updateCodeFromForm(false);
        updatePreview();
    });

    themeSelect.addEventListener('change', function () {
        updateCodeFromForm(true);
        updatePreview();
    });

    designSelect.addEventListener('change', function () {
        updateCodeFromForm(true);
        updatePreview();
    });

    codeBox.addEventListener('input', function () {
        syncFormFromCode();
        updatePreview();
    });

    document.querySelectorAll('[data-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-tab]').forEach(function (item) { item.classList.remove('active'); });
            tab.classList.add('active');
            const showCode = tab.dataset.tab === 'code';
            document.getElementById('preview-stage').hidden = showCode;
            document.getElementById('code-stage').hidden = !showCode;
        });
    });

    const toggle = document.getElementById('toggle-sidebar');
    toggle?.addEventListener('click', function () {
        const shell = document.querySelector('.editor-shell');
        shell.classList.toggle('hide-sidebar');
        toggle.textContent = shell.classList.contains('hide-sidebar') ? 'Show editor' : 'Hide editor';
    });

    if (codeBox.value.trim()) {
        syncFormFromCode();

        const savedPage = new DOMParser().parseFromString(codeBox.value, 'text/html');
        const savedMain = savedPage.querySelector('[data-theme]');
        if (savedMain && !savedMain.dataset.design) {
            savedMain.dataset.design = designSelect.value;
            const savedStyle = savedPage.querySelector('style');
            if (savedStyle) {
                savedStyle.textContent += (designCss[designSelect.value] || designCss.studio)
                + unboxedDesignCss;
            }
            codeBox.value = '<!doctype html>\n' + savedPage.documentElement.outerHTML;
        } else if (savedMain) {
            const savedStyle = savedPage.querySelector('style');
            if (savedStyle && !savedStyle.textContent.includes('.skill-group')) {
                savedStyle.textContent += unboxedDesignCss;
                codeBox.value = '<!doctype html>\n' + savedPage.documentElement.outerHTML;
            }
        }
    } else {
        codeBox.value = generateHtml();
    }
    updateCodeFromForm(false);
    updatePreview();
});
