import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/customer-order-checkout.css",
                "resources/css/customer-order-form.css",
                "resources/css/handai-kasir-checkout.css",
                "resources/css/handai-manager-finance-dashboard.css",
                "resources/css/handai-manager-inventory-recipes-create.css",
                "resources/css/handai-manager-inventory-stock-create.css",
                "resources/css/handai-manager-inventory-stock.css",
                "resources/css/handai-manager-operational-stock-movements.css",
                "resources/css/handai-pos-dashboard.css",
                "resources/css/handai-pos-history.css",
                "resources/css/handai-pos-layout.css"
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});