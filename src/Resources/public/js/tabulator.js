const initTabulator = (tableConfig) => {
    return new Promise((resolve, reject) => {
        let table = new Tabulator(tableConfig.selector, tableConfig.options);

        // Overwrite ajaxContentType to send X-Request-Generator header
        // https://github.com/olifolkerd/tabulator/blob/master/src/js/modules/Ajax/defaults/contentTypeFormatters.js
        if (table.options["ajaxContentType"] === "json") {
            table.options["ajaxContentType"] = {
                headers: {
                    "Content-Type": "application/json",
                    "X-Request-Generator": "tabulator"
                },
                body: function (url, config, params) {
                    return JSON.stringify(params);
                },
            }
        }

        // Wait for initialization
        table.on("tableBuilt", () => {
            resolve(table);
        });
    });
}

export { initTabulator };
