class ReviewMenu
{
    #tocItems;
    #html;

    constructor() {
        this.#tocItems = document.querySelectorAll('[data-type="toc-item"]');
        this.#html = document.querySelector('html');
    }

    init() {
        this.#tocItems.forEach((tocItem) => {
            const item = document.querySelector(tocItem.querySelector('a').hash);
            const itemTop = item.getBoundingClientRect().top + window.scrollY;
            if (itemTop + window.innerHeight < document.documentElement.scrollHeight) {
                tocItem.dataset.isReachedY = itemTop;
            } else {
                tocItem.dataset.isReachedY = item.getBoundingClientRect().bottom + window.scrollY;
            }
        });

        document.addEventListener('scroll', (ev) => {
            this.#tocItems.forEach((tocItem) => {
                if (window.scrollY + window.innerHeight > tocItem.dataset.isReachedY) {
                    tocItem.classList.add('-active');
                } else {
                    tocItem.classList.remove('-active');
                }
            })
        });
    }
}