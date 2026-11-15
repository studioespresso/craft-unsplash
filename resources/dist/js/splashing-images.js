/**
 * The Unsplash browser: an Inertia page owned by the plugin (ImagesController renders `splashing-images/Gallery`).
 * Owning the page is what lets it fill layout slots, like the search box next to the title.
 *
 * A plain component with a template, which the Control Panel's Vue compiles at runtime,
 * so this file needs no build step. Masonry is CSS columns, infinite scroll an IntersectionObserver.
 */
(() => {
    // The CP's shared module (http client with CSRF header, notices), resolved through its import map
    const cpModule = () => import('@craftcms/cp');

    const Gallery = {
        name: 'SplashingImagesGallery',
        props: {
            images: {type: Array, required: true},
            nextPageUrl: {type: String, default: null},
            searchUrl: {type: String, required: true},
            query: {type: String, default: null},
            labels: {type: Object, required: true},
        },
        data() {
            return {
                items: [...this.images],
                next: this.nextPageUrl,
                search: this.query ?? '',
                loading: false,
                saving: {},
            };
        },
        watch: {
            // A new search visits the same page component with fresh props
            images(images) {
                this.items = [...images];
                this.next = this.nextPageUrl;
                this.search = this.query ?? '';
            },
        },
        mounted() {
            this.observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    this.loadMore();
                }
            }, {rootMargin: '400px'});
            this.observer.observe(this.$refs.sentinel);
        },
        beforeUnmount() {
            this.observer?.disconnect();
        },
        methods: {
            submitSearch() {
                const url = new URL(this.searchUrl, window.location.href);
                if (this.search.trim() !== '') {
                    url.searchParams.set('search', this.search.trim());
                }
                window.Cp?.$router?.get ? window.Cp.$router.get(url.toString()) : window.location.assign(url);
            },
            async loadMore() {
                if (this.loading || !this.next) {
                    return;
                }
                this.loading = true;
                try {
                    const {http} = await cpModule();
                    const {data} = await http.get(this.next, {headers: {Accept: 'application/json'}});
                    this.items.push(...data.images);
                    this.next = data.nextPageUrl;
                } catch (error) {
                    this.next = null;
                    (await cpModule()).showError(error?.response?.data?.message ?? String(error));
                } finally {
                    this.loading = false;
                }
            },
            async save(image) {
                if (this.saving[image.id]) {
                    return;
                }
                this.saving[image.id] = true;
                const {http, getCpUrl, showNotice, showError} = await cpModule();
                try {
                    const {data} = await http.post(getCpUrl('splashing-images/download'), {id: image.id});
                    data.success ? showNotice(data.message) : showError(data.message);
                } catch (error) {
                    showError(error?.response?.data?.message ?? String(error));
                } finally {
                    delete this.saving[image.id];
                }
            },
        },
        template: `
            <component is="craft:cp-container" class="splashing">
                <!-- Next to the page title -->
                <component is="craft:layout-slot" name="content-actions">
                    <form class="splashing-search" role="search" @submit.prevent="submitSearch">
                        <craft-input type="search" :value="search" @input="search = $event.target.value" :placeholder="labels.search" :label="labels.search" label-sr-only></craft-input>
                        <craft-button type="submit">{{ labels.search }}</craft-button>
                    </form>
                </component>
                <p v-if="!items.length" class="splashing-status">{{ labels.empty }}</p>
                <div class="splashing-grid">
                    <div v-for="image in items" :key="image.id" class="splashing-item" :class="{saving: saving[image.id]}">
                        <button type="button" class="splashing-image" :title="labels.save" @click="save(image)">
                            <img :src="image.small" :alt="labels.photoBy.replace('{name}', image.attr.name)" loading="lazy">
                        </button>
                        <craft-spinner v-if="saving[image.id]" class="splashing-spinner"></craft-spinner>
                        <a class="splashing-attribute" :href="image.attr.link" target="_blank" rel="noopener">{{ image.attr.name }}</a>
                    </div>
                </div>
                <div ref="sentinel" class="splashing-status">
                    <craft-spinner v-if="loading"></craft-spinner>
                    <span v-else-if="!next && items.length">{{ labels.end }}</span>
                </div>
            </component>
        `,
    };

    window.Cp.booting((cp) => {
        cp.$inertia.register('splashing-images/Gallery', Gallery);
    });
})();
