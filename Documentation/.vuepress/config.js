module.exports = {
    title: 'Neos Backups',
    themeConfig: {
        nav: [
            {text: 'Github', link: 'https://github.com/breadlesscode/neos-backups'}
        ],
        sidebar: [
            '/installation',
            '/filesystems',
            '/steps',
            '/custom-behaviours'
        ],
        theme: '@vuepress/theme-default',
        smoothScroll: true,
        docsRepo: 'breadlesscode/neos-backups',
        docsDir: 'Documentation',
        docsBranch: 'master',
        editLinks: true,
        editLinkText: 'Help me improve this page!',
        lastUpdated: 'Last Updated',
    }
};