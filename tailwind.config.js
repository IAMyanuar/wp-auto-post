const { iconsPlugin } = require("@iconify/tailwind4");

export default {
    darkMode: "class",
    content: ["./resources/**/*.blade.php", "./resources/**/*.js"],
    safelist: [
        // Sidebar dynamic classes
        "text-sky-400", "bg-sky-400", "animate-pulse",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Outfit", "ui-sans-serif", "system-ui", "sans-serif"],
                outfit: ["Outfit", "sans-serif"],
            },
            colors: {
                // ─── Semantic theme tokens ───────────────────────────────────────
                // Edit the HSL values below to change the app's color scheme.

                // Primary: Main brand color. Default = Black (0°, 0%, 9%)
                primary:         "hsl(var(--primary))",
                "primary-hover": "hsl(var(--primary-hover))",
                "primary-fg":    "hsl(var(--primary-fg))",

                // Surface: Background layers
                "surface-1": "hsl(var(--surface-1))",
                "surface-2": "hsl(var(--surface-2))",
                "surface-3": "hsl(var(--surface-3))",

                // Border
                border:          "hsl(var(--border))",
                "border-light":  "hsl(var(--border-light))",

                // Text
                "text-base":    "hsl(var(--text-base))",
                "text-muted":   "hsl(var(--text-muted))",
                "text-subtle":  "hsl(var(--text-subtle))",

                // Semantic Status Colors (keep colors vibrant, only adjust HSL)
                accent:          "hsl(var(--accent))",
                "accent-fg":     "hsl(var(--accent-fg))",
                success:         "hsl(var(--success))",
                "success-fg":    "hsl(var(--success-fg))",
                warning:         "hsl(var(--warning))",
                "warning-fg":    "hsl(var(--warning-fg))",
                danger:          "hsl(var(--danger))",
                "danger-fg":     "hsl(var(--danger-fg))",
            },
            borderRadius: {
                "card": "var(--radius-card)",
                "btn":  "var(--radius-btn)",
            },
            boxShadow: {
                "card": "var(--shadow-card)",
                "card-lg": "var(--shadow-card-lg)",
                "nav": "var(--shadow-nav)",
            },
        },
    },
    plugins: [iconsPlugin()],
};
