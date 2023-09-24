class ReviewMenu
{
    #tocItems;
    #breakpoints;

    constructor() {
        this.#tocItems = document.querySelectorAll('[data-type="toc-item"]');
        this.#breakpoints = new Map();
    }

    init() {
        this.#tocItems.forEach((tocItem) => {
            const item = document.querySelector(tocItem.querySelector('a').hash);
            // const itemTop = item.getBoundingClientRect().top + window.scrollY;
            this.#breakpoints.set(item.id, item.getBoundingClientRect().bottom + window.scrollY);
            // if (itemTop + window.innerHeight < document.documentElement.scrollHeight) {
            //     tocItem.dataset.isReachedY = itemTop;
            // } else {
            //     tocItem.dataset.isReachedY = item.getBoundingClientRect().bottom + window.scrollY;
            // }
        });

        document.addEventListener('scroll', (ev) => {
            this.#tocItems.forEach((tocItem) => {
                const breakpoint = this.#breakpoints.get(tocItem.querySelector('a').hash.substring(1));
                if (window.scrollY + window.innerHeight > breakpoint) {
                    tocItem.classList.add('-active');
                } else {
                    tocItem.classList.remove('-active');
                }
            })
        });
    }
}