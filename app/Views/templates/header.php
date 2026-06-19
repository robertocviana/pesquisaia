<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?>PesquisaIA</title>
    <meta name="description" content="<?= isset($description) ? htmlspecialchars($description) : 'Pesquisas inteligentes em minutos.' ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(\App\Helpers\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#6366f1',
                            foreground: '#f9fafb',
                            soft: '#eef2ff',
                        },
                        background: '#fafafa',
                        foreground: '#1e1b4b',
                        card: '#ffffff',
                        'card-foreground': '#1e1b4b',
                        muted: {
                            DEFAULT: '#f3f4f6',
                            foreground: '#6b7280',
                        },
                        accent: {
                            DEFAULT: '#eef2ff',
                            foreground: '#4338ca',
                        },
                        border: '#e5e7eb',
                        input: '#e5e7eb',
                        ring: '#6366f1',
                        success: {
                            DEFAULT: '#22c55e',
                            foreground: '#ffffff',
                        },
                        warning: {
                            DEFAULT: '#eab308',
                            foreground: '#713f12',
                        },
                        destructive: {
                            DEFAULT: '#ef4444',
                            foreground: '#ffffff',
                        },
                        sidebar: {
                            DEFAULT: '#fafafa',
                            foreground: '#1e1b4b',
                            border: '#e5e7eb',
                        },
                    },
                    borderRadius: {
                        DEFAULT: '0.75rem',
                        sm: '0.5rem',
                        md: '0.625rem',
                        lg: '0.75rem',
                        xl: '1rem',
                        '2xl': '1.25rem',
                    },
                    boxShadow: {
                        soft: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.06)',
                        elevated: '0 4px 6px -1px rgb(15 23 42 / 0.06), 0 10px 20px -10px rgb(15 23 42 / 0.10)',
                        pop: '0 20px 40px -20px rgb(99 102 241 / 0.35)',
                    },
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        * { border-color: #e5e7eb; }
        html, body {
            background-color: #fafafa;
            color: #1e1b4b;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>

<body>
