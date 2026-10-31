<x-layouts.app title="Theme Settings">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div class="theme-manager-header">
            <div>
                <h1 style="font-size: 25px; color: var(--xp-text);">Choose your theme</h1>
                <p style="margin-top: 6px; color: var(--xp-muted); font-size: 13px;">
                    Select a built-in palette or create your own and customize its colors.
                </p>
            </div>
            <button type="button" class="btn btn-primary" id="add-theme">＋ Create theme</button>
        </div>

        <p class="theme-manager-message" id="theme-manager-message" role="status" aria-live="polite"></p>

        <section class="card" aria-labelledby="theme-options-title">
            <div class="card-header">
                <div>
                    <h2 class="card-title" id="theme-options-title">Available themes</h2>
                    <p class="card-subtitle">Built-in themes are fixed. Your custom themes can be edited or deleted.</p>
                </div>
            </div>
            <div class="theme-options" id="theme-options"></div>
            <p class="theme-storage-note">
                Custom themes and your selection are stored only in this browser. They do not change match data or predictions.
            </p>
        </section>

        <section class="card theme-editor" id="theme-editor" aria-labelledby="theme-editor-title" hidden>
            <div class="card-header">
                <div>
                    <h2 class="card-title" id="theme-editor-title">Create theme</h2>
                    <p class="card-subtitle">Changes preview immediately. Save to keep them in this browser.</p>
                </div>
            </div>

            <form id="theme-form">
                <label for="theme-name" style="display:block;margin-bottom:6px;color:var(--xp-muted);font-weight:600;">Theme name</label>
                <input class="form-control" id="theme-name" name="name" type="text" maxlength="32" required style="width:100%;max-width:420px;">

                <label for="theme-mode" style="display:block;margin:14px 0 6px;color:var(--xp-muted);font-weight:600;">Base appearance</label>
                <select class="select-pill" id="theme-mode" name="mode">
                    <option value="dark">Dark</option>
                    <option value="light">Light</option>
                </select>

                <fieldset style="border:0;margin-top:14px;">
                    <legend style="color:var(--xp-text);font-weight:700;">Palette colors</legend>
                    <div class="theme-editor-grid" id="theme-color-fields"></div>
                </fieldset>

                <div class="theme-editor-actions">
                    <button class="btn btn-primary" type="submit" id="save-theme">Save theme</button>
                    <button class="btn btn-secondary" type="button" id="cancel-theme">Cancel</button>
                </div>
            </form>
        </section>
    </div>

    <script>
        const themeManager = window.XpectThemeManager;
        const themeOptions = document.getElementById('theme-options');
        const themeEditor = document.getElementById('theme-editor');
        const themeForm = document.getElementById('theme-form');
        const themeMessage = document.getElementById('theme-manager-message');
        const themeName = document.getElementById('theme-name');
        const themeMode = document.getElementById('theme-mode');
        const themeColorFields = document.getElementById('theme-color-fields');
        const paletteFields = [
            ['page', 'Page background'],
            ['sidebar', 'Sidebar'],
            ['surface', 'Cards and panels'],
            ['surfaceHover', 'Raised surfaces'],
            ['surfaceActive', 'Active surfaces'],
            ['border', 'Borders'],
            ['borderSubtle', 'Subtle borders'],
            ['borderLight', 'Highlighted borders'],
            ['text', 'Main text'],
            ['muted', 'Muted text'],
            ['away', 'Chart gray'],
            ['accent', 'Accent'],
            ['accentHover', 'Accent hover'],
            ['accentPress', 'Accent pressed'],
        ];
        let editingThemeId = null;
        let draftColors = null;
        let themeBeforeEditing = null;

        function showThemeMessage(message, isError = false) {
            themeMessage.textContent = message;
            themeMessage.style.color = isError ? 'var(--xp-error)' : 'var(--xp-muted)';
        }

        function makeActionButton(label, className, action) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = className;
            button.textContent = label;
            button.addEventListener('click', action);
            return button;
        }

        function activateTheme(theme) {
            try {
                themeManager.activate(theme.id);
                renderThemes();
                showThemeMessage(`${theme.name} is now active.`);
            } catch (error) {
                showThemeMessage(error.message, true);
            }
        }

        function renderThemes() {
            const activeTheme = themeManager.getActiveTheme();
            themeOptions.replaceChildren();

            themeManager.getThemes().forEach((theme) => {
                const card = document.createElement('article');
                card.className = 'theme-card';

                const selectButton = document.createElement('button');
                selectButton.type = 'button';
                selectButton.className = 'theme-option';
                selectButton.setAttribute('aria-pressed', String(theme.id === activeTheme.id));
                selectButton.setAttribute('data-theme-choice', theme.id);
                selectButton.addEventListener('click', () => activateTheme(theme));

                const swatch = document.createElement('span');
                swatch.className = 'theme-swatch';
                swatch.style.setProperty('--preview-page', theme.colors.page);
                swatch.style.setProperty('--preview-surface', theme.colors.surface);
                swatch.style.setProperty('--preview-accent', theme.colors.accent);
                swatch.setAttribute('aria-hidden', 'true');

                const panel = document.createElement('span');
                panel.className = 'theme-swatch-panel';
                const accent = document.createElement('span');
                accent.className = 'theme-swatch-accent';
                swatch.append(panel, accent);

                const copy = document.createElement('span');
                copy.className = 'theme-option-copy';
                const title = document.createElement('span');
                title.className = 'theme-option-title';
                title.textContent = theme.name;
                const description = document.createElement('span');
                description.className = 'theme-option-description';
                description.textContent = theme.description || (theme.id.startsWith('custom-') ? 'Custom palette' : '');
                const status = document.createElement('span');
                status.className = 'theme-option-status';
                status.textContent = theme.id === activeTheme.id ? 'CURRENT THEME' : '';
                copy.append(title, description, status);
                selectButton.append(swatch, copy);
                card.append(selectButton);

                if (theme.id.startsWith('custom-')) {
                    const actions = document.createElement('div');
                    actions.className = 'theme-option-actions';
                    actions.append(
                        makeActionButton('Edit', 'btn btn-secondary', () => openEditor(theme)),
                        makeActionButton('Delete', 'btn btn-secondary', () => deleteTheme(theme)),
                    );
                    card.append(actions);
                }

                themeOptions.append(card);
            });
        }

        function fillColorFields(colors) {
            themeColorFields.replaceChildren();
            paletteFields.forEach(([key, label]) => {
                const wrapper = document.createElement('label');
                wrapper.className = 'theme-color-field';
                wrapper.htmlFor = `theme-color-${key}`;
                const text = document.createElement('span');
                text.textContent = label;
                const input = document.createElement('input');
                input.type = 'color';
                input.id = `theme-color-${key}`;
                input.name = key;
                input.value = colors[key];
                input.addEventListener('input', () => {
                    draftColors[key] = input.value.toUpperCase();
                    previewDraft();
                });
                wrapper.append(text, input);
                themeColorFields.append(wrapper);
            });
        }

        function previewDraft() {
            const draft = {
                id: editingThemeId || 'custom-preview',
                name: themeName.value.trim() || 'Custom preview',
                mode: themeMode.value,
                colors: draftColors,
            };
            Object.entries(themeManager.colors).forEach(([key, variable]) => {
                document.documentElement.style.setProperty(variable, draft.colors[key]);
            });
            document.documentElement.dataset.theme = draft.id;
            document.documentElement.dataset.themeMode = draft.mode;
            document.documentElement.style.colorScheme = draft.mode;
        }

        function openEditor(theme = null) {
            const sourceTheme = theme || themeManager.getActiveTheme();
            themeBeforeEditing = themeManager.getActiveTheme();
            editingThemeId = theme ? theme.id : null;
            draftColors = { ...sourceTheme.colors };
            themeForm.reset();
            themeName.value = theme ? theme.name : `${sourceTheme.name} Custom`;
            themeMode.value = sourceTheme.mode;
            document.getElementById('theme-editor-title').textContent = theme ? 'Edit custom theme' : 'Create theme';
            document.getElementById('save-theme').textContent = theme ? 'Save changes' : 'Save theme';
            fillColorFields(draftColors);
            themeEditor.hidden = false;
            showThemeMessage(theme ? `Editing ${theme.name}.` : 'Creating a custom theme.');
            previewDraft();
            themeEditor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            themeName.focus();
        }

        function closeEditor() {
            themeEditor.hidden = true;
            editingThemeId = null;
            draftColors = null;
            const activeTheme = themeBeforeEditing;
            themeBeforeEditing = null;
            try {
                themeManager.activate(activeTheme.id);
            } catch (error) {
                showThemeMessage(error.message, true);
            }
            renderThemes();
        }

        function deleteTheme(theme) {
            if (!window.confirm(`Delete the custom theme "${theme.name}"?`)) {
                return;
            }

            const wasActive = themeManager.getActiveTheme().id === theme.id;
            const remainingThemes = themeManager.getThemes()
                .filter((candidate) => candidate.id.startsWith('custom-') && candidate.id !== theme.id);
            try {
                themeManager.saveCustomThemes(remainingThemes);
                if (wasActive) {
                    themeManager.activate('pitch-black');
                }
                renderThemes();
                showThemeMessage(`${theme.name} was deleted.`);
            } catch (error) {
                showThemeMessage(error.message, true);
            }
        }

        themeName.addEventListener('input', previewDraft);
        themeMode.addEventListener('change', previewDraft);
        document.getElementById('add-theme').addEventListener('click', () => openEditor());
        document.getElementById('cancel-theme').addEventListener('click', closeEditor);
        themeForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const name = themeName.value.trim();
            if (!name) {
                showThemeMessage('Enter a name for this theme.', true);
                themeName.focus();
                return;
            }

            const currentCustomThemes = themeManager.getThemes()
                .filter((theme) => theme.id.startsWith('custom-'));
            const id = editingThemeId || `custom-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
            const updatedTheme = {
                id,
                name,
                description: 'Custom palette',
                mode: themeMode.value,
                colors: { ...draftColors },
            };
            const nextCustomThemes = editingThemeId
                ? currentCustomThemes.map((theme) => theme.id === editingThemeId ? updatedTheme : theme)
                : [...currentCustomThemes, updatedTheme];

            try {
                themeManager.saveCustomThemes(nextCustomThemes);
                themeManager.activate(updatedTheme.id);
                themeEditor.hidden = true;
                editingThemeId = null;
                draftColors = null;
                themeBeforeEditing = null;
                renderThemes();
                showThemeMessage(`${updatedTheme.name} was saved and applied.`);
            } catch (error) {
                showThemeMessage(error.message, true);
            }
        });

        renderThemes();
    </script>
</x-layouts.app>
