<script>
    (() => {
        const storageKey = 'theme';
        const systemTheme = () => {
            const mediaQuery = window.matchMedia?.('(prefers-color-scheme: dark)');

            return mediaQuery?.matches ? 'dark' : 'light';
        };

        let theme = systemTheme();

        try {
            const stored = localStorage.getItem(storageKey);

            if (stored === 'light' || stored === 'dark') {
                theme = stored;
            }
        } catch (error) {
            theme = systemTheme();
        }

        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.style.colorScheme = theme;
        document.documentElement.classList.add('js-enabled');
    })();
</script>
