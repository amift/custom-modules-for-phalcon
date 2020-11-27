{{ get_doctype() }}
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>Skeleton Application MyTest</title>
        <meta name="description" content="Skeleton Application MyTest" />
        <meta name="keywords" content="Skeleton Application MyTest" />
        {{ get_title() }}
        {{ stylesheet_link("css/style.css") }}
    </head>
    <body>
        <div class="wrapper">
            <section id="content">
                {{ content() }}
            </section>
        </div>
    </body>
</html>