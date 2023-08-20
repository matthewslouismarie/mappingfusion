class ReviewMenu
{
    #links;

    constructor() {
        this.#links = document.querySelectorAll('[data-type="review-menu-link"]');
        this.#links.forEach((link) => {
            link.dataset.reviewSectionBottom = document.querySelector(link.hash).getBoundingClientRect().top;
        });
    }

    init() {
        document.addEventListener('scroll', (ev) => {
            console.log(window.scrollY);
            this.#links.forEach((link) => {
                if (window.scrollY > link.dataset.reviewSectionBottom) {
                    link.classList.add('-active');
                } else {
                    link.classList.remove('-active');
                }
            })
        });
    }
}