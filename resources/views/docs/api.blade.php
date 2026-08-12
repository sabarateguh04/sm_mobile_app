<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Smart Backoffice</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Highlight JS CSS for Code Snippets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
    <!-- Github Markdown CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown.min.css">
    <style>
        .markdown-body {
            box-sizing: border-box;
            min-width: 200px;
            max-width: 980px;
            margin: 0 auto;
            padding: 45px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        @media (max-width: 767px) {
            .markdown-body {
                padding: 15px;
            }
        }
        body { background: #f3f4f6; }
    </style>
</head>
<body class="py-8">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex justify-between items-center max-w-[980px] mx-auto">
            <h1 class="text-2xl font-bold text-gray-800">Smart Backoffice API Portal</h1>
            <a href="/" class="text-blue-600 hover:underline">&larr; Kembali ke Home</a>
        </div>
        <article class="markdown-body" id="content">
            <!-- Content will be rendered here by marked.js -->
        </article>
    </div>

    <!-- Marked.js -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- Highlight.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        var rawMarkdown = {!! json_encode($markdown) !!};
        
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    return hljs.highlight(code, { language: lang }).value;
                }
                return hljs.highlightAuto(code).value;
            }
        });

        document.getElementById('content').innerHTML = marked.parse(rawMarkdown);
    </script>
</body>
</html>
