class BibControl{
    constructor() {
        this.listener()
    }

    listener() {
        $("#content_wrapper").on("click", "#pageButton", function () {
            let url = $(this).data("url");

            $.ajax({
                type: "GET",
                dataType: "json",
                url: url,
                success: function (data) {
                    thisObject.reloadResults(data);
                }
            })

        });



    }


    reloadResults(data) {
        let resultTemplate = twig({
            href: "localhost:8000/templates/viewer/bibliography/bib.results.twig",
            async: true,
            load: function (template) {
                let resultHtml = template.render({data: data});
                $("#results_area").html("");

            }
        })
    }
}