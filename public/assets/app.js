(function () {
    const state = {
        data: window.AGENT_DATA || null,
    };

    const selectors = {
        profile: document.getElementById('profile-card'),
        platformCatalog: document.getElementById('platform-catalog'),
        routines: document.getElementById('routines'),
        comparativeTableHead: document.querySelector('#comparative-table thead'),
        comparativeTableBody: document.querySelector('#comparative-table tbody'),
        missions: document.getElementById('missions'),
        status: document.getElementById('status-message'),
        refreshBtn: document.getElementById('refresh-btn'),
    };

    function clearNode(node) {
        if (!node) return;
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    }

    function renderProfile(profile) {
        const container = selectors.profile;
        if (!container) return;
        clearNode(container);

        const title = document.createElement('h3');
        title.textContent = `${profile.name} — ${profile.brand}`;

        const meta = document.createElement('div');
        meta.className = 'profile-meta';

        const site = document.createElement('span');
        site.textContent = `Sites : ${profile.site_main} | ${profile.site_brand}`;
        const taglineFr = document.createElement('span');
        taglineFr.textContent = `Tagline FR : ${profile.tagline_fr}`;
        const taglineEn = document.createElement('span');
        taglineEn.textContent = `Tagline EN : ${profile.tagline_en}`;
        const exp = document.createElement('span');
        exp.textContent = `Expérience : ${profile.years_experience} ans`;

        meta.append(site, taglineFr, taglineEn, exp);

        const pillarsTitle = document.createElement('h4');
        pillarsTitle.textContent = 'Piliers de contenu';

        const pillarsList = document.createElement('div');
        pillarsList.className = 'badge-list';
        (profile.content_pillars || []).forEach((pillar) => {
            const badge = document.createElement('span');
            badge.className = 'badge';
            badge.textContent = pillar;
            pillarsList.appendChild(badge);
        });

        const socialTitle = document.createElement('h4');
        socialTitle.textContent = 'Présence en ligne';

        const socials = document.createElement('div');
        socials.className = 'social-links';
        const socialLinks = profile.social_links || {};
        Object.keys(socialLinks).forEach((network) => {
            const link = document.createElement('a');
            link.href = socialLinks[network];
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = `${network}`;
            socials.appendChild(link);
        });

        container.append(title, meta);
        if ((profile.content_pillars || []).length > 0) {
            container.append(pillarsTitle, pillarsList);
        }
        if (Object.keys(socialLinks).length > 0) {
            container.append(socialTitle, socials);
        }
    }

    function renderPlatformCatalog(catalog, topPlatforms) {
        const container = selectors.platformCatalog;
        if (!container) return;
        clearNode(container);

        Object.keys(catalog).forEach((type) => {
            const group = document.createElement('div');
            group.className = 'platform-group';

            const title = document.createElement('h3');
            title.textContent = type;
            group.appendChild(title);

            catalog[type].forEach((platform) => {
                const item = document.createElement('div');
                item.className = 'platform-item';

                const label = document.createElement('strong');
                label.textContent = platform.label;
                const notes = document.createElement('p');
                notes.textContent = platform.notes;
                notes.className = 'platform-note';

                item.append(label, notes);
                group.appendChild(item);
            });

            container.appendChild(group);
        });

        if ((topPlatforms || []).length > 0) {
            const topBlock = document.createElement('div');
            topBlock.className = 'top-platforms';
            const heading = document.createElement('h3');
            heading.textContent = 'Top 5 focus';
            topBlock.appendChild(heading);

            const list = document.createElement('ol');
            topPlatforms.forEach((platform) => {
                const li = document.createElement('li');
                li.textContent = platform;
                list.appendChild(li);
            });

            topBlock.appendChild(list);
            container.appendChild(topBlock);
        }
    }

    function renderRoutines(routines) {
        const container = selectors.routines;
        if (!container) return;
        clearNode(container);

        const daily = document.createElement('div');
        daily.className = 'routine-block';
        const dailyTitle = document.createElement('h3');
        dailyTitle.textContent = routines.daily.title;
        daily.appendChild(dailyTitle);

        const dailyList = document.createElement('ul');
        dailyList.className = 'routine-list';
        routines.daily.steps.forEach((step) => {
            const li = document.createElement('li');
            li.textContent = step;
            dailyList.appendChild(li);
        });
        daily.appendChild(dailyList);

        const boost = document.createElement('div');
        boost.className = 'routine-block';
        const boostTitle = document.createElement('h3');
        boostTitle.textContent = routines.boost.title;
        boost.appendChild(boostTitle);

        routines.boost.days.forEach((day) => {
            const dayTitle = document.createElement('h4');
            dayTitle.textContent = day.label;
            boost.appendChild(dayTitle);

            const list = document.createElement('ul');
            list.className = 'routine-list';
            day.actions.forEach((action) => {
                const li = document.createElement('li');
                li.textContent = action;
                list.appendChild(li);
            });
            boost.appendChild(list);
        });

        container.append(daily, boost);
    }

    function renderComparativeTable(rows) {
        const head = selectors.comparativeTableHead;
        const body = selectors.comparativeTableBody;
        if (!head || !body) return;
        clearNode(head);
        clearNode(body);

        const headerRow = document.createElement('tr');
        ['Plateforme', 'Difficulté', 'Gains potentiels', 'Rapidité', 'Compétition', 'Idéal pour', 'Notes'].forEach((label) => {
            const th = document.createElement('th');
            th.textContent = label;
            headerRow.appendChild(th);
        });
        head.appendChild(headerRow);

        rows.forEach((row) => {
            const tr = document.createElement('tr');
            ['platform', 'difficulty', 'earnings', 'speed', 'competition', 'ideal_for', 'notes'].forEach((key) => {
                const td = document.createElement('td');
                td.textContent = row[key];
                tr.appendChild(td);
            });
            body.appendChild(tr);
        });
    }

    function renderMissions(missions) {
        const container = selectors.missions;
        if (!container) return;
        clearNode(container);

        missions.forEach((mission) => {
            const card = document.createElement('article');
            card.className = 'mission-card';

            const header = document.createElement('div');
            header.className = 'mission-header';
            const title = document.createElement('h3');
            title.textContent = `${mission.job.platform} — Mission #${mission.index + 1}`;
            header.appendChild(title);

            const meta = document.createElement('div');
            meta.className = 'mission-meta';
            meta.innerHTML = `
                <span>Langue : ${mission.language.toUpperCase()}</span>
                <span>Score : ${mission.strategy.score} (${mission.strategy.grade})</span>
            `;
            header.appendChild(meta);

            const analysisBlock = document.createElement('div');
            analysisBlock.className = 'mission-analysis';
            const analysisTitle = document.createElement('h4');
            analysisTitle.textContent = 'Analyse rapide';
            analysisBlock.appendChild(analysisTitle);

            const analysisList = document.createElement('ul');
            const tech = mission.analysis.technologies.length > 0
                ? mission.analysis.technologies.join(', ')
                : 'à préciser';

            const items = [
                `Objectif : ${mission.analysis.goal}`,
                `Techno : ${tech}`,
                `Deadline : ${mission.analysis.deadline}`,
                `Budget : ${mission.analysis.budget !== null ? mission.analysis.budget + ' €' : 'Non communiqué'}`,
            ];
            if (mission.analysis.red_flags.length > 0) {
                items.push(`Risques : ${mission.analysis.red_flags.join(' / ')}`);
            }

            items.forEach((item) => {
                const li = document.createElement('li');
                li.textContent = item;
                analysisList.appendChild(li);
            });
            analysisBlock.appendChild(analysisList);

            const strategyBlock = document.createElement('div');
            strategyBlock.className = 'mission-strategy';
            const strategyTitle = document.createElement('h4');
            strategyTitle.textContent = 'Stratégie';
            strategyBlock.appendChild(strategyTitle);

            const strategyDetails = document.createElement('p');
            strategyDetails.textContent = mission.strategy.explanation;
            strategyBlock.appendChild(strategyDetails);

            const pitchBlock = document.createElement('div');
            pitchBlock.className = 'pitch-block';
            pitchBlock.textContent = mission.pitch;

            card.append(header, analysisBlock, strategyBlock, pitchBlock);
            container.appendChild(card);
        });
    }

    function render(data) {
        if (!data) return;
        renderProfile(data.profile);
        renderPlatformCatalog(data.platformCatalog, data.profile.top_platforms || []);
        renderRoutines(data.routines);
        renderComparativeTable(data.comparativeTable);
        renderMissions(data.missions);
    }

    async function refreshData() {
        const button = selectors.refreshBtn;
        if (!button) return;
        button.disabled = true;
        setStatus('Rafraîchissement en cours…');

        try {
            const response = await fetch('api/jobs.php', { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const payload = await response.json();
            if (payload.status !== 'ok') {
                throw new Error(payload.message || 'Erreur lors du chargement');
            }
            state.data = payload.data;
            render(state.data);
            setStatus('Missions mises à jour avec succès.');
        } catch (error) {
            console.error(error);
            setStatus(`Erreur : ${error instanceof Error ? error.message : 'inconnue'}`);
        } finally {
            button.disabled = false;
        }
    }

    function setStatus(message) {
        if (selectors.status) {
            selectors.status.textContent = message || '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        render(state.data);
        setStatus('Données chargées depuis job.json.');
        if (selectors.refreshBtn) {
            selectors.refreshBtn.addEventListener('click', refreshData);
        }
    });
})();
