
(function() {

/**
 * Removes html
 * @param {string} res 
 */
function removeExtension(res) {
    if (res.endsWith(".html")) {
        return res.slice(0, -5);
    }
    return res;
}


function beautify(str) {
    if (str === "rekarel") return "ReKarel";
    if (str.length === 0) return str;
    str = str.replace(/_/g, " ");
    str = str.charAt(0).toUpperCase() + str.slice(1);
    return str;
}
/**
 * 
 * @param {string[]} links
 */
function setBreadcrumbs(links) {
    const $breadcrumb = $("#breadcrumb");

    let url = $('body').attr('data-root');
    links.forEach((el, idx) => {
        url += `/${el}`;
        
        // Create elements with jQuery
        const $li = $("<li>").addClass("breadcrumb-item");
        const $a = $("<a>")
            .text(beautify(removeExtension(el)))
            .addClass("link-body-emphasis text-decoration-none")
            .attr("href", url);
        
        // Append anchor to list item and list item to breadcrumb
        $li.append($a);
        $breadcrumb.append($li);
    });
}

/**
 * 
 * @param {string[]} links
 */
function SetNavBar(links) {
    if (links.length === 0 || links[0]==="") {
        $("#intro_nav").addClass("bg-primary-subtle");
        $("#intro_nav").addClass("border");
        $("#intro_nav").addClass("");
        return;
    }
    let current = $("#nav_list");
    for (let i =0; i < links.length; i++) {
        const el = removeExtension(links[i]);
        if (i == links.length - 1) {
            const tail = current.find(`[data-crumb="${el}"]`);
            if (tail.length >0 && tail.hasClass("collapse")) {                
                bootstrap.Collapse.getOrCreateInstance(tail[0]).show();
            }
            current = current.find(`[data-end-crumb="${el}"]`);
            current.addClass("border");
            current.addClass("bg-primary-subtle");
            continue;
        }
        const head = current.find(`[data-end-crumb="${el}"]`)
        current = current.find(`[data-crumb="${el}"]`);
        if (current.length===0) 
            return;
        head.addClass("nav-active-head")
        head.addClass("bg-body-tertiary")
        head.addClass("border")
        if (current.hasClass("collapse"))
            bootstrap.Collapse.getOrCreateInstance(current[0]).show();
    }
}

function getPath() {
    const url = window.location.href.replace(/#.*/g, '');;
    const parts = url.split('/');
    const docsIndex = parts.indexOf('docs');
    return parts.slice(docsIndex + 1).filter(element => element !== "");
}
setBreadcrumbs(getPath());
SetNavBar(getPath());

function setTheme(theme) {
    $(document.body).attr("data-bs-theme", theme);
    if (localStorage) {
        localStorage.setItem("docs:theme", theme);
    }
    if (theme === "dark") {
        $("#dayLightSet").prop("checked", true);
        $("#dayLightIcon").removeClass("bi-brightness-high-fill");
        $("#dayLightIcon").addClass("bi-moon-fill");
    } else {        
        $("#dayLightSet").prop("checked", false);
        $("#dayLightIcon").addClass("bi-brightness-high-fill");
        $("#dayLightIcon").removeClass("bi-moon-fill");
    }
}

// Daylight
$("#dayLightSet").on("change", ()=> {
    if ($("#dayLightSet").prop("checked")) {
        setTheme("dark");
    } else {
        setTheme("light");
    }
});

if (localStorage) {
    const theme = localStorage.getItem("docs:theme");
    if (theme) {        
        setTheme(theme); 
    } else {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme("dark"); 
        } else {
            setTheme("light");
        }
    }
} else if (window.matchMedia) {
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        setTheme("dark"); 
    } else {
        setTheme("light");
    }
}


function buildTableOfContents() {
    const table = $("#toc");
    const anchors = $("[data-toc]");
    const uls = [table]
    anchors.each(function() {
        const target = $(this);
        if (target.attr("data-toc-depth") > uls.length) {
            //Generate a nested ul
            const li_ul = $("<li>");
            const ul = $("<ul>");
            li_ul.append(ul);
            uls[uls.length - 1].append(li_ul);
            uls.push(ul);
        }
        while (target.attr("data-toc-depth") < uls.length) {
            uls.pop();
        }
        const li = $("<li>");

        const a = $("<a>")
            .attr("href", "#"+target.attr("id"))
            .text(target.attr("data-toc"));
        li.append(a);
        uls[uls.length - 1].append(li)
    })
}

buildTableOfContents();


})();