class Menu
{
    #buttonId;
    #containerHtmlId;
    #menuId;

    constructor(buttonId, containerhtmlId, menuId) {
        this.#containerHtmlId = containerhtmlId;
        this.#buttonId = buttonId;
        this.#menuId = menuId;
    }

    init() {
        const btn = document.getElementById(this.#buttonId);
        btn.style.display = 'block';
        btn.onclick = (ev) => {
            document.getElementById(this.#menuId).classList.add('-open');
        };
    }
}