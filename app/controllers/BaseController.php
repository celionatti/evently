<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Controller\Controller;


abstract class BaseController extends Controller
{
    public function onConstruct()
    {
        // Set common performance and SEO optimizations
        $this->setupPerformanceOptimizations();
        $this->setupDefaultMeta();
    }

    private function setupPerformanceOptimizations()
    {
        // Add resource hints for better performance
        $this->view->addLink('preconnect', 'https://fonts.googleapis.com')
                   ->addLink('preconnect', 'https://fonts.gstatic.com', ['crossorigin' => 'true'])
                   ->addLink('preconnect', 'https://cdn.jsdelivr.net')
                   ->addLink('dns-prefetch', '//fonts.googleapis.com')
                   ->addLink('dns-prefetch', '//cdn.jsdelivr.net');

        // Add critical CSS inline for above-the-fold content
        $this->view->addStyle('
            /* Critical CSS for faster loading */
            .flash-message {
                position: relative;
                z-index: 1050;
                animation: slideDown 0.3s ease-out;
            }
            
            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            /* Loading state */
            .loading {
                opacity: 0.6;
                pointer-events: none;
                position: relative;
            }
            
            .loading::after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #007bff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        ');

        // Add performance monitoring script
        $this->view->addInlineScript('
            // Performance monitoring
            window.addEventListener("load", function() {
                if ("performance" in window) {
                    const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                    console.log("Page load time:", loadTime + "ms");
                    
                    // Send to analytics if available
                    if (typeof gtag !== "undefined") {
                        gtag("event", "page_load_time", {
                            value: Math.round(loadTime),
                            custom_parameter: "page_performance"
                        });
                    }
                }
            });
        ');
    }

    private function setupDefaultMeta()
    {
        // Set default meta tags for all pages
        $this->view->setAuthor("Eventlyy Team")
                   ->setViewport("width=device-width, initial-scale=1.0")
                   ->setMetaTag('theme-color', '#007bff')
                   ->setMetaTag('application-name', 'Eventlyy')
                   ->setMetaTag('msapplication-TileColor', '#007bff')
                   ->setMetaTag('msapplication-config', '/browserconfig.xml')
                   ->setMetaTag('apple-mobile-web-app-capable', 'yes')
                   ->setMetaTag('apple-mobile-web-app-status-bar-style', 'default')
                   ->setMetaTag('apple-mobile-web-app-title', 'Eventlyy')
                   ->setMetaTag('format-detection', 'telephone=no');

        // Add manifest and other PWA elements
        $this->view->addLink('manifest', '/site.webmanifest')
                   ->addLink('mask-icon', '/safari-pinned-tab.svg', ['color' => '#007bff']);
    }

    protected function addAnalytics()
    {
        // Add Google Analytics or other tracking
        $this->view->addInlineScript('
            // Google Analytics 4
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag("js", new Date());
            gtag("config", "GA_MEASUREMENT_ID", {
                page_title: document.title,
                page_location: window.location.href
            });
        ');

        $this->view->addScript('https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID', [
            'async' => 'true'
        ]);
    }

    protected function addSocialShareButtons()
    {
        // Add social sharing functionality
        $this->view->addInlineScript('
            function shareOnSocial(platform, url, text) {
                const shareUrls = {
                    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
                    twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`,
                    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`,
                    whatsapp: `https://wa.me/?text=${encodeURIComponent(text + " " + url)}`
                };
                
                if (shareUrls[platform]) {
                    window.open(shareUrls[platform], "_blank", "width=600,height=400");
                }
            }
            
            // Native Web Share API support
            function shareNative(title, text, url) {
                if (navigator.share) {
                    navigator.share({
                        title: title,
                        text: text,
                        url: url
                    });
                } else {
                    // Fallback to copy to clipboard
                    navigator.clipboard.writeText(url).then(() => {
                        alert("Link copied to clipboard!");
                    });
                }
            }
        ');
    }
}