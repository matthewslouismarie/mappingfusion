class ReviewMenu
{
    #tocItems;
    #breakpoints;

    constructor() {
        this.#tocItems = document.querySelectorAll('[data-type=toc-item]');
        this.#breakpoints = Object.create(null);
    }

    init() {
        this.#tocItems.forEach((tocItem) => {
            const hash = tocItem.querySelector('a').hash;
            const item = document.querySelector(hash);
            this.#breakpoints[hash] = item.offsetTop;
        });

        document.addEventListener('scroll', (ev) => {
            this.#tocItems.forEach((tocItem) => {
                const breakpoint = this.#breakpoints[tocItem.querySelector('a').hash];
                if (window.scrollY + 0.5 * window.innerHeight > breakpoint) {
                    tocItem.classList.add('-active');
                } else {
                    tocItem.classList.remove('-active');
                }
            })
        });
    }
}

window.addEventListener('load', function () {
    const reviewMenu = new ReviewMenu();
    reviewMenu.init();
});