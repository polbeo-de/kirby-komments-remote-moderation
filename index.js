panel.plugin('polbeo/kirby-komments-remote-moderation', {
  components: {
    'k-remote-komments-view': {
      props: {
        sources: {
          type: Array,
          default: () => [],
        },
      },
      data() {
        return {
          selectedSourceId: this.sources[0]?.id ?? null,
          entries: [],
          loading: false,
        };
      },
      computed: {
        selectedSource() {
          return this.sources.find((source) => source.id === this.selectedSourceId) ?? null;
        },
        rows() {
          return this.entries.map((entry) => ({
            id: entry.id,
            author: entry.authorName,
            page: entry.pageTitle,
            type: entry.type,
            updatedAt: entry.updatedAt,
            options: this.optionsFor(entry),
          }));
        },
        columns() {
          return {
            author: { label: this.$t('polbeo.komments-remote-moderation.entry.author'), type: 'text' },
            page: { label: this.$t('polbeo.komments-remote-moderation.entry.page'), type: 'text' },
            type: { label: this.$t('polbeo.komments-remote-moderation.entry.type'), type: 'text' },
            updatedAt: { label: this.$t('polbeo.komments-remote-moderation.entry.updated-at'), type: 'text' },
          };
        },
      },
      watch: {
        selectedSourceId() {
          this.load();
        },
      },
      mounted() {
        this.load();
      },
      methods: {
        async load() {
          if (!this.selectedSourceId) {
            this.entries = [];
            return;
          }
          this.loading = true;
          try {
            const response = await this.$panel.api.get(`komments-remote-moderation/${this.selectedSourceId}/pending`);
            this.entries = Array.isArray(response.data)
              ? response.data.filter((entry) => entry.status !== 'PUBLISHED')
              : [];
          } catch (_error) {
            this.entries = [];
            this.$panel.notification.error(this.$t('polbeo.komments-remote-moderation.error.remote-unavailable'));
          } finally {
            this.loading = false;
          }
        },
        optionsFor(entry) {
          const primaryAction = entry.status === 'PUBLISHED' ? 'hold' : 'publish';
          return [
            { text: this.$t(`polbeo.komments-remote-moderation.action.${primaryAction}`), icon: primaryAction === 'publish' ? 'check' : 'pause', click: () => this.moderate(entry, primaryAction) },
            { text: this.$t('polbeo.komments-remote-moderation.action.spam'), icon: 'flag', click: () => this.moderate(entry, 'spam') },
            '-',
            { text: this.$t('polbeo.komments-remote-moderation.action.delete'), icon: 'trash', theme: 'negative', click: () => this.moderate(entry, 'delete') },
          ];
        },
        async moderate(entry, action) {
          if (this.selectedSource?.live !== true || window.confirm(this.$t('polbeo.komments-remote-moderation.action.live-confirmation'))) {
            try {
              await this.$panel.api.post(`komments-remote-moderation/${this.selectedSourceId}/moderate`, {
                id: entry.id,
                action,
                updatedAt: entry.updatedAt,
              });
              await this.load();
            } catch (error) {
              const key = error?.response?.status === 409
                ? 'polbeo.komments-remote-moderation.error.conflict'
                : 'polbeo.komments-remote-moderation.error.remote-unavailable';
              this.$panel.notification.error(this.$t(key));
            }
          }
        },
      },
      render(h) {
        const text = (key) => this.$t(key);
        const children = [h('div', { class: 'k-remote-komments-heading' }, [
          h('h1', [text('polbeo.komments-remote-moderation.area.label')]),
          h('p', [this.selectedSource?.label || '']),
        ])];

        if (this.sources.length === 0) {
          children.push(h('div', { class: 'k-remote-komments-empty' }, [text('polbeo.komments-remote-moderation.source.none')]));
          return h('k-panel-inside', [h('div', { class: 'k-remote-komments-view' }, children)]);
        }

        children.push(h('div', { class: 'k-remote-komments-toolbar' }, [
          h('label', { class: 'k-remote-komments-source' }, [
            h('span', [text('polbeo.komments-remote-moderation.source.label')]),
            h('select', { domProps: { value: this.selectedSourceId }, on: { change: (event) => { this.selectedSourceId = event.target.value; } } }, this.sources.map((source) => h('option', { domProps: { value: source.id } }, [source.label]))),
          ]),
          h('button', { class: 'k-remote-komments-refresh', attrs: { type: 'button', disabled: this.loading }, on: { click: this.load } }, [text('polbeo.komments-remote-moderation.action.refresh')]),
        ]));
        if (this.selectedSource?.live) {
          children.push(h('div', { class: 'k-remote-komments-live' }, [text('polbeo.komments-remote-moderation.source.live-warning')]));
        }
        if (this.entries.length === 0) {
          children.push(h('div', { class: 'k-remote-komments-empty' }, [this.loading ? text('polbeo.komments-remote-moderation.action.loading') : text('polbeo.komments-remote-moderation.entry.empty')]));
        } else {
          children.push(h('table', { class: 'k-remote-komments-table' }, [
            h('thead', [h('tr', [
              h('th', [text('polbeo.komments-remote-moderation.entry.author')]), h('th', [text('polbeo.komments-remote-moderation.entry.page')]), h('th', [text('polbeo.komments-remote-moderation.entry.type')]), h('th', [text('polbeo.komments-remote-moderation.entry.updated-at')]), h('th', ['Kommentar']), h('th', ['Aktionen']),
            ])]),
            h('tbody', this.entries.map((entry) => h('tr', { key: entry.id }, [
              h('td', [entry.authorName || '—']), h('td', [entry.pageUrl ? h('a', { attrs: { href: entry.pageUrl, target: '_blank', rel: 'noopener noreferrer' } }, [entry.pageTitle || '—']) : (entry.pageTitle || '—')]), h('td', [h('span', { class: 'k-remote-komments-status' }, [entry.type])]), h('td', [entry.updatedAt]), h('td', { class: 'k-remote-komments-content' }, [entry.content]),
              h('td', [h('div', { class: 'k-remote-komments-actions' }, ['publish', 'hold', 'spam', 'delete'].map((action) => h('button', { attrs: { type: 'button', 'data-action': action }, on: { click: () => this.moderate(entry, action) } }, [text(`polbeo.komments-remote-moderation.action.${action}`)])))]),
            ]))),
          ]));
        }
        return h('k-panel-inside', [h('div', { class: 'k-remote-komments-view' }, children)]);
      },
    },
  },
  fields: {
    'remote-komments': {
      props: {
        pagePath: { type: String, required: true },
        sources: { type: Array, default: () => [] },
      },
      data() {
        return { selectedSourceId: this.sources[0]?.id ?? null, entries: [], loading: false };
      },
      watch: {
        selectedSourceId() { this.load(); },
      },
      mounted() { this.load(); },
      methods: {
        actionsFor(entry) {
          return entry.status === 'PUBLISHED'
            ? ['hold', 'spam', 'delete']
            : ['publish', 'spam', 'delete'];
        },
        async load() {
          if (!this.selectedSourceId) {
            this.entries = [];
            return;
          }
          this.loading = true;
          try {
            const path = encodeURIComponent(this.pagePath);
            const response = await this.$panel.api.get(`komments-remote-moderation/${this.selectedSourceId}/pending-for-page?path=${path}`);
            this.entries = Array.isArray(response.data) ? response.data : [];
          } catch (_error) {
            this.entries = [];
            this.$panel.notification.error(this.$t('polbeo.komments-remote-moderation.error.remote-unavailable'));
          } finally {
            this.loading = false;
          }
        },
        async moderate(entry, action) {
          const source = this.sources.find((candidate) => candidate.id === this.selectedSourceId);
          if (source?.live === true && !window.confirm(this.$t('polbeo.komments-remote-moderation.action.live-confirmation'))) {
            return;
          }
          try {
            await this.$panel.api.post(`komments-remote-moderation/${this.selectedSourceId}/moderate`, {
              id: entry.id, action, updatedAt: entry.updatedAt,
            });
            await this.load();
          } catch (error) {
            const key = error?.response?.status === 409
              ? 'polbeo.komments-remote-moderation.error.conflict'
              : 'polbeo.komments-remote-moderation.error.remote-unavailable';
            this.$panel.notification.error(this.$t(key));
          }
        },
      },
      render(h) {
        const text = (key) => this.$t(key);
        const controls = [
          h('select', {
            attrs: { 'aria-label': text('polbeo.komments-remote-moderation.source.label') },
            domProps: { value: this.selectedSourceId },
            on: { change: (event) => { this.selectedSourceId = event.target.value; } },
          }, this.sources.map((source) => h('option', { domProps: { value: source.id } }, [source.label]))),
          h('button', { attrs: { type: 'button', disabled: this.loading }, on: { click: this.load } }, [text('polbeo.komments-remote-moderation.action.refresh')]),
        ];
        const content = this.entries.length === 0
          ? h('div', { class: 'k-remote-komments-empty' }, [this.loading ? text('polbeo.komments-remote-moderation.action.loading') : text('polbeo.komments-remote-moderation.entry.empty')])
          : h('table', { class: 'k-remote-komments-table' }, [
            h('thead', [h('tr', [h('th', [text('polbeo.komments-remote-moderation.entry.author')]), h('th', ['Kommentar']), h('th', [text('polbeo.komments-remote-moderation.entry.status')]), h('th', ['Aktionen'])])]),
            h('tbody', this.entries.map((entry) => h('tr', { key: entry.id }, [
              h('td', [entry.authorName || '—']),
              h('td', { class: 'k-remote-komments-content' }, [entry.content]),
              h('td', [entry.status || '—']),
              h('td', [h('div', { class: 'k-remote-komments-actions' }, this.actionsFor(entry).map((action) => h('button', { attrs: { type: 'button' }, on: { click: () => this.moderate(entry, action) } }, [text(`polbeo.komments-remote-moderation.action.${action}`)])))]),
            ]))),
          ]);
        return h('k-field', {
          props: { label: this.$t('polbeo.komments-remote-moderation.section.label') },
        }, [
          this.sources.length === 0
            ? h('div', { class: 'k-remote-komments-empty' }, [text('polbeo.komments-remote-moderation.source.none')])
            : h('div', { class: 'k-remote-komments-section' }, [h('div', { class: 'k-remote-komments-toolbar' }, controls), content]),
        ]);
      },
    },
  },
});
