<?php

declare(strict_types=1);

namespace Trees\Helper\Meta;

/**
 * =======================================
 * ***************************************
 * ========== Trees Meta Class ===========
 * ***************************************
 * A comprehensive SEO meta tag generator with:
 * - Automatic title generation
 * - Smart description extraction
 * - Keyword analysis
 * - Open Graph and Twitter Card support
 * - Canonical URL handling
 *
 * Features:
 * - Advanced text analysis
 * - SEO best practices
 * - Social media integration
 * - Multilingual support
 * =======================================
 */

class Meta
{
    private const DEFAULT_TITLE_LENGTH = 60;
    private const DEFAULT_DESC_LENGTH = 160;
    private const DEFAULT_KEYWORD_LIMIT = 15;
    private const MIN_WORD_LENGTH = 3;

    /**
     * Generate an SEO-friendly meta title with keyword optimization
     */
    public static function generateTitle(
        string $title,
        string $content = '',
        int $maxLength = self::DEFAULT_TITLE_LENGTH,
        bool $includeSiteName = true,
        string $siteName = '',
        string $separator = '|'
    ): string {
        // Clean the title
        $title = self::cleanText($title);

        // Extract primary keywords if content is provided
        $keywords = [];
        if (!empty($content)) {
            $keywords = self::extractKeywords($content, 3);

            // Add relevant keywords not already in title
            foreach ($keywords as $keyword) {
                if (!self::containsWord($title, $keyword)) {
                    $title .= " $separator " . ucfirst($keyword);
                    break; // Add just one keyword to avoid spam
                }
            }
        }

        // Add site name if requested
        if ($includeSiteName && !empty($siteName)) {
            $title .= " $separator " . $siteName;
        }

        // Limit title length for SEO best practices
        return self::truncateSmart($title, $maxLength);
    }

    /**
     * Generate a meta description optimized for SEO
     */
    public static function generateDescription(
        string $content,
        int $maxLength = self::DEFAULT_DESC_LENGTH,
        bool $useFirstParagraph = true
    ): string {
        // Clean HTML tags and get plain text
        $cleanedContent = self::cleanText($content);

        // Extract summary based on strategy
        $summary = $useFirstParagraph
            ? self::extractFirstParagraph($cleanedContent)
            : self::summarizeContent($cleanedContent);

        // Trim summary to specified length
        return self::truncateSmart($summary, $maxLength);
    }

    /**
     * Generate optimized meta keywords
     */
    public static function generateKeywords(
        string $title,
        string $content = '',
        int $limit = self::DEFAULT_KEYWORD_LIMIT,
        array $additionalKeywords = []
    ): string {
        // Combine sources for keyword extraction
        $textSources = [self::cleanText($title)];
        if (!empty($content)) {
            $textSources[] = self::cleanText($content);
        }

        // Extract keywords from combined text
        $keywords = self::extractKeywords(implode(' ', $textSources), $limit);

        // Merge with additional keywords
        if (!empty($additionalKeywords)) {
            $keywords = array_unique(array_merge(
                $keywords,
                array_map('strtolower', $additionalKeywords)
            ));
            $keywords = array_slice($keywords, 0, $limit);
        }

        return implode(', ', $keywords);
    }

    /**
     * Generate canonical URL with proper formatting
     */
    public static function generateCanonicalUrl(
        string $baseUrl,
        ?string $additionalPath = null,
        array $queryParams = [],
        bool $https = true
    ): string {
        // Normalize base URL
        $url = rtrim($baseUrl, '/');

        // Add path if provided
        if ($additionalPath) {
            $url .= '/' . ltrim($additionalPath, '/');
        }

        // Add query parameters if any
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        // Force HTTPS if requested
        if ($https && strpos($url, 'http://') === 0) {
            $url = 'https://' . substr($url, 7);
        }

        return $url;
    }

    /**
     * Generate complete meta tags including Open Graph and Twitter Cards
     */
    public static function generateMetaTags(array $options): string
    {
        $tags = [];

        // Required fields validation
        if (empty($options['title'])) {
            throw new \InvalidArgumentException('Title is required for meta tags');
        }

        // Basic meta tags
        $tags[] = self::generateTitleTag($options['title']);

        if (!empty($options['description'])) {
            $tags[] = self::generateDescriptionTag($options['description']);
        }

        if (!empty($options['keywords'])) {
            $tags[] = self::generateKeywordsTag($options['keywords']);
        }

        if (!empty($options['canonical'])) {
            $tags[] = self::generateCanonicalTag($options['canonical']);
        }

        // Open Graph tags
        if (!empty($options['og'])) {
            $tags = array_merge($tags, self::generateOpenGraphTags($options['og']));
        }

        // Twitter Card tags
        if (!empty($options['twitter'])) {
            $tags = array_merge($tags, self::generateTwitterCardTags($options['twitter']));
        }

        // Additional meta tags
        if (!empty($options['meta'])) {
            foreach ($options['meta'] as $name => $content) {
                $tags[] = self::generateMetaTag($name, $content);
            }
        }

        return implode("\n", $tags);
    }

    /**
     * Generate Open Graph meta tags
     */
    public static function generateOpenGraphTags(array $ogData): array
    {
        $required = ['title', 'type', 'url'];
        foreach ($required as $field) {
            if (empty($ogData[$field])) {
                throw new \InvalidArgumentException("og:$field is required for Open Graph tags");
            }
        }

        $tags = [];
        $defaults = [
            'site_name' => '',
            'description' => '',
            'image' => '',
            'image:width' => '1200',
            'image:height' => '630',
            'locale' => 'en_US'
        ];

        $ogData = array_merge($defaults, $ogData);

        foreach ($ogData as $property => $content) {
            if (!empty($content)) {
                $prop = strpos($property, ':') === false
                    ? "og:$property"
                    : "og:$property";
                $tags[] = self::generateMetaTag($prop, $content, 'property');
            }
        }

        return $tags;
    }

    /**
     * Generate Twitter Card meta tags
     */
    public static function generateTwitterCardTags(array $twitterData): array
    {
        $tags = [];
        $defaults = [
            'card' => 'summary_large_image',
            'site' => '',
            'creator' => '',
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        $twitterData = array_merge($defaults, $twitterData);

        foreach ($twitterData as $name => $content) {
            if (!empty($content)) {
                $tags[] = self::generateMetaTag("twitter:$name", $content);
            }
        }

        return $tags;
    }

    /**
     * Extract keywords from text with advanced analysis
     */
    protected static function extractKeywords(string $text, int $limit): array
    {
        // Clean and normalize text
        $text = self::cleanText($text);

        // Split text into words
        $words = preg_split('/\s+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        // Filter words
        $stopWords = self::getStopWords();
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) >= self::MIN_WORD_LENGTH &&
                   !in_array($word, $stopWords) &&
                   !is_numeric($word);
        });

        // Count word frequency
        $wordCount = array_count_values($filteredWords);
        arsort($wordCount);

        // Extract top keywords
        $keywords = array_keys(array_slice($wordCount, 0, $limit));

        // Score keywords by position and frequency
        $scoredKeywords = [];
        foreach ($keywords as $keyword) {
            $score = $wordCount[$keyword];

            // Boost score if keyword appears in first paragraph
            if (strpos($text, $keyword) < (strlen($text) * 0.2)) {
                $score *= 1.5;
            }

            $scoredKeywords[$keyword] = $score;
        }

        arsort($scoredKeywords);
        return array_keys($scoredKeywords);
    }

    /**
     * Extract the first paragraph from content
     */
    protected static function extractFirstParagraph(string $content): string
    {
        // Split into paragraphs
        $paragraphs = preg_split('/\n\s*\n/', trim($content));

        // Return first non-empty paragraph
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                return $paragraph;
            }
        }

        return '';
    }

    /**
     * Summarize content by extracting key sentences
     */
    protected static function summarizeContent(string $content): string
    {
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // Score sentences by length and position
        $scoredSentences = [];
        foreach ($sentences as $i => $sentence) {
            $length = strlen($sentence);
            $positionScore = 1 - ($i / count($sentences));
            $scoredSentences[$i] = $length * $positionScore;
        }

        // Get top 2 sentences
        arsort($scoredSentences);
        $topIndices = array_keys(array_slice($scoredSentences, 0, 2));
        sort($topIndices);

        $summary = '';
        foreach ($topIndices as $index) {
            $summary .= $sentences[$index] . ' ';
        }

        return trim($summary);
    }

    /**
     * Clean text by removing unwanted characters and formatting
     */
    protected static function cleanText(string $text): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Replace multiple spaces/newlines with single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove special characters except basic punctuation
        $text = preg_replace('/[^\w\s\-.,;:!?\'"]/', '', $text);

        return trim($text);
    }

    /**
     * Smart truncation that preserves words and adds ellipsis
     */
    protected static function truncateSmart(string $text, int $maxLength): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        // Find last space before max length
        $truncated = mb_substr($text, 0, $maxLength);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        // Remove any trailing punctuation
        $truncated = rtrim($truncated, '.,;:!?');

        return $truncated . '...';
    }

    /**
     * Check if string contains a word (whole word match)
     */
    protected static function containsWord(string $text, string $word): bool
    {
        return preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text) === 1;
    }

    /**
     * Get list of stop words to exclude from keyword analysis
     */
    protected static function getStopWords(): array
    {
        return [
            // English stop words
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'if', 'in',
            'into', 'is', 'it', 'no', 'not', 'of', 'on', 'or', 'such', 'that', 'the',
            'their', 'then', 'there', 'these', 'they', 'this', 'to', 'was', 'will', 'with',

            // Common SEO terms to exclude
            'read', 'more', 'click', 'here', 'view', 'page', 'website', 'web', 'site',
            'online', 'internet', 'article', 'post', 'blog', 'content', 'info', 'information'
        ];
    }

    /**
     * Helper method to generate a standard meta tag
     */
    private static function generateMetaTag(
        string $name,
        string $content,
        string $attribute = 'name'
    ): string {
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        return "<meta {$attribute}=\"{$name}\" content=\"{$content}\">";
    }

    /**
     * Generate title tag
     */
    private static function generateTitleTag(string $title): string
    {
        return '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>';
    }

    /**
     * Generate description meta tag
     */
    private static function generateDescriptionTag(string $description): string
    {
        return self::generateMetaTag('description', $description);
    }

    /**
     * Generate keywords meta tag
     */
    private static function generateKeywordsTag(string $keywords): string
    {
        return self::generateMetaTag('keywords', $keywords);
    }

    /**
     * Generate canonical link tag
     */
    private static function generateCanonicalTag(string $url): string
    {
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return "<link rel=\"canonical\" href=\"{$url}\">";
    }
}