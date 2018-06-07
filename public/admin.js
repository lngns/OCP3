$(function () {
    tinyMCE.init({selector:"textarea"});
    $("button[phoc\\:action=article-delete]").click(function () {
        $(this).text("Êtes-vous sûr?");
        $(this).click(() => {
            let id = $(this).attr("phoc:val");
            $.post(PHOC.BaseUrl + "/_service/deleteArticle", {
                article: id,
                Ene: true
            }).done(() => {
                $("#article-title-" + id).html("Supprimé");
                $("#article-actions-" + id).html("-");
            }).fail(quit);
        });
    });
    $("button[phoc\\:action=edit-abort]").click(function () {
        let notRebindedFunc = () => window.history.back();
        $(this).text("Toutes vos modifications seront perdues; êtes-vous sûr?");
        $(this).removeClass("btn-warning").addClass("btn-danger");
        $(this).click(notRebindedFunc);
        setTimeout(() => {
            $(this).off("click", notRebindedFunc);
            $(this).removeClass("btn-danger").addClass("btn-warning");
            $(this).text("Annuler");
        }, 5000);
    });
    $("button[phoc\\:action=report-delete]").click(function () {
        $(this).text("Êtes vous sûr?");
        $(this).click(() => {
            let id = $(this).attr("phoc:value");
            $.post(PHOC.BaseUrl + "/_service/deleteComment", {
                comment: id,
                Ene: true
            }).done(() => {
                $("#report-title-" + id).html("Supprimé");
                $("#report-author-" + id).html("-");
                $("#report-date-" + id).html("-");
                $("#report-actions-" + id).html("-");
            }).fail(quit);
        });
    });
    /*$("button[phoc\\:action=article-write]").click(function () {

    });*/
    const publish = function () {
        $.post(PHOC.BaseUrl + "/_service/publish", {
            article: $(this).attr("phoc:val"),
            Ene: true
        }).done(() => {
            $(this).off("click", publish).click(unpublish);
            $(this).removeClass("btn-success").addClass("btn-info").html("Dépublier");
        }).fail(quit);
    };
    const unpublish = function () {
        $.post(PHOC.BaseUrl + "/_service/unpublish", {
            article: $(this).attr("phoc:val"),
            Ene: true
        }).done(() => {
            $(this).off("click", unpublish).click(publish);
            $(this).removeClass("btn-info").addClass("btn-success").html("Publier");
        }).fail(quit);
    };
    $("button[phoc\\:action=article-unpublish]").click(unpublish);
    $("button[phoc\\:action=article-publish]").click(publish);

    function quit(req) {
        if(req.status === 403)
            window.location.href = PHOC.BaseUrl + "/admin?session";
    }
});