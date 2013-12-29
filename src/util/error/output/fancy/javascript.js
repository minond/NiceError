(function() {
    var highlight, matchers, len, i, files,
        nodes = document.querySelectorAll("table td pre");

    matchers = {
        ml_comment_start: { r: /^\s{0,}(\/\*\*)/ },
        ml_comment_body: { r: /^\s{0,}(\*.{0,})/, o: ["annotations"] },
        ml_comment_end: { r: /^\s{0,}(\*\/)/ },
        sl_comment: { r: /^\s{0,}(\/\/.+)/, o: ["annotations"] },
        func_calls: { r: /([a-zA-Z_]+?)\(/ },
        func_names: { r: /function\s+([a-zA-Z_]+)/ },
        strings: { r: /('.+?'|".+?")/ },
        variables: { r: /(\$[a-zA-Z\$_]+)/ },
        keywords: { r: /(public|protected|static|function|use |namespace |new |if|do|while|foreach|for| as |break|return|class | extends | implements |throw |echo|die|exit|abstract |final |interface |self|null|try|catch|case|default|switch|endif|endwhile|endfor|endforeach|else|elseif|trait |require|require_once|include|include_once|list)/ },
        constant: { r: /(true|false|array|object|bool|boolean|int|integer|string|double|float|__METHOD__|__DIR__|__NAMESPACE__|__FUNCTION__|__CLASS__|__FILE__|__LINE__)/ },
        annotations: { r: /^\s{0,}\*\s+(\@\w+)/, o: ["annotation_at"] },
        annotation_at: { r: /^\s{0,}\*\s+(\@)\w+/ },
        characters: { r: /(\{|\}|\(|\)|\[|\])/ },
        properties: { r: /\-\>([a-zA-Z_]+)/ },
        class_names: { r: /class\s+|new\s+([a-zA-Z_]+)[::]{0}/ },
        specials: { r: /(\@|\&)/ },
        numbers: { r: /([0-9]+)/ }
    };

    highlight = function(node) {
        var matcher, match, child, here, getwrapper, getregex, html,
            cleanup = document.createElement("div"), str=node.innerHTML;

        getwrapper = function(klass, html) {
            return "<span class='hi hi-" + klass + "'>" + html + "</span>";
        };

        getregex = function(html) {
            return new RegExp(html.replace(/(\W)/g, "\\$1"), "g");
        };

        for (var matcher in matchers) {
            if (child && child.indexOf(matcher) === -1) {
                continue;
            }

            if (node.innerText) {
                str = node.innerText;
            }

            (function replace(str) {
                if (match = str.match(matchers[ matcher ].r)) {
                    if (!match[1]) {
                        return;
                    }

                    if (matchers[ matcher ].o) {
                        child = matchers[ matcher ].o;
                    }

                    html = match[1];
                    here = match.index + html.length;
                    cleanup.innerHTML = html;
                    html = cleanup.innerHTML;

                    node.innerHTML = node.innerHTML.replace(
                        html, getwrapper(matcher, html));
                    node.innerHTML = node.innerHTML.replace(
                        getregex(html), getwrapper(matcher, html));

                    // more matches?
                    replace(str.substr(here));
                }
            })(str);
        }
    };

    for (i = 0, len = nodes.length; i < len; i++) {
        highlight(nodes[i]);
    }

    // view source
    files = document.querySelectorAll(".files article");

    for (i = 0, len = files.length; i < len; i++) {
        files[i].addEventListener("click", function() {
            var sourcesel, sources,
                index = Array.prototype.slice
                    .call(this.parentNode.children, 0)
                    .indexOf(this);

            sources = document.querySelectorAll(".source section");
            sourcesel = ".source section:nth-of-type(" + (index + 1) + ")";

            for (i = 0, len = sources.length; i < len; i++) {
                sources[i].style.display = "none";
            }

            for (i = 0, len = files.length; i < len; i++) {
                files[i].classList.remove("selected");
            }

            this.classList.add("selected");
            document.querySelector(sourcesel).style.display = "block";
        });
    }
})();
