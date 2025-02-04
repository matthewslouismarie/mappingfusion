const smdeOptions = document.getElementById('smde-init').dataset;
const autosaveDateTimeKey = smdeOptions.autosaveDateTimeKey;
const smdeCacheId = smdeOptions.smdeCacheId;
const lastUpdateDateTimeUtc = smdeOptions.lastUpdateDateTimeUtc;

if ('' !== lastUpdateDateTimeUtc && null !== localStorage.getItem(autosaveDateTimeKey)) {
    const lastUpdateDateTime = new Date(parseInt(lastUpdateDateTimeUtc));
    const autosaveDateTime = new Date(parseInt(localStorage.getItem(autosaveDateTimeKey)));
    if (lastUpdateDateTime > autosaveDateTime) {
        localStorage.removeItem(`smde_${smdeCacheId}`);
        localStorage.removeItem(autosaveDateTimeKey);
    }
}

var simplemde = new SimpleMDE({
    autosave: {
        enabled: true,
        delay: 5000,
        uniqueId: smdeCacheId,
    },
    element: document.getElementById("content"),
    spellChecker: false,
});

document.getElementById("content").required = false;

const changes = new UnsavedChanges('submit-button', 'article-form');
simplemde.codemirror.on('change', function() {
    changes.init();
});
simplemde.codemirror.on("change", function () {
    localStorage.setItem(autosaveDateTimeKey, Date.now());
});