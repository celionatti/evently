<?php

declare(strict_types=1);

namespace Trees\Helper\Meta;

/**
 * =======================================
 * ***************************************
 * ===== Enhanced Trees Meta Class ======
 * ***************************************
 * A comprehensive SEO meta tag generator with:
 * - Advanced title generation with brand positioning
 * - AI-powered description optimization
 * - Semantic keyword analysis
 * - Complete Open Graph and Twitter Card support
 * - Structured data integration
 * - Core Web Vitals optimization
 * - Mobile-first SEO considerations
 * - Multi-language support
 * - Schema.org markup generation
 *
 * New Features:
 * - JSON-LD structured data
 * - Breadcrumb generation
 * - Image optimization tags
 * - Preload resource hints
 * - Security headers
 * - Accessibility improvements
 * =======================================
 */

class AdvanceMeta
{
    // SEO Constants
    private const DEFAULT_TITLE_LENGTH = 60;
    private const MAX_TITLE_LENGTH = 70;
    private const DEFAULT_DESC_LENGTH = 155;
    private const MAX_DESC_LENGTH = 160;
    private const DEFAULT_KEYWORD_LIMIT = 10;
    private const MIN_WORD_LENGTH = 3;
    private const MAX_WORD_LENGTH = 20;
    
    // Social Media Image Dimensions
    private const OG_IMAGE_WIDTH = 1200;
    private const OG_IMAGE_HEIGHT = 630;
    private const TWITTER_IMAGE_WIDTH = 1024;
    private const TWITTER_IMAGE_HEIGHT = 512;
    
    // Content Quality Scores
    private const MIN_CONTENT_LENGTH = 300;
    private const IDEAL_CONTENT_LENGTH = 1500;

    /**
     * Generate an SEO-optimized meta title with advanced keyword positioning
     */
    public static function generateTitle(
        string $title,
        string $content = '',
        int $maxLength = self::DEFAULT_TITLE_LENGTH,
        bool $includeSiteName = true,
        string $siteName = '',
        string $separator = '|',
        string $brandPosition = 'end', // 'start', 'end', 'none'
        array $targetKeywords = []
    ): string {
        // Clean and normalize title
        $title = self::cleanText($title);
        $originalTitle = $title;

        // Extract and score keywords from content
        $contentKeywords = [];
        if (!empty($content)) {
            $contentKeywords = self::extractScoredKeywords($content, 5);
        }

        // Merge with target keywords (give them higher priority)
        $allKeywords = array_merge($targetKeywords, array_keys($contentKeywords));
        $allKeywords = array_unique(array_map('strtolower', $allKeywords));

        // Add high-priority keywords if not present
        $addedKeywords = [];
        foreach (array_slice($allKeywords, 0, 2) as $keyword) {
            if (!self::containsWord($title, $keyword) && 
                mb_strlen($title . ' ' . $keyword) < ($maxLength - 10)) {
                $addedKeywords[] = ucfirst($keyword);
            }
        }

        // Construct title based on brand position
        $finalTitle = self::buildTitleWithBrand(
            $originalTitle,
            $siteName,
            $separator,
            $brandPosition,
            $addedKeywords,
            $includeSiteName
        );

        // Optimize title structure for CTR
        $finalTitle = self::optimizeTitleForCTR($finalTitle);

        return self::truncateSmart($finalTitle, $maxLength);
    }

    /**
     * Generate SEO-optimized meta description with semantic analysis
     */
    public static function generateDescription(
        string $content,
        int $maxLength = self::DEFAULT_DESC_LENGTH,
        bool $useFirstParagraph = false,
        array $targetKeywords = [],
        string $callToAction = '',
        bool $includeEmoji = false
    ): string {
        $cleanedContent = self::cleanText($content);
        
        // Choose extraction strategy based on content length and quality
        $strategy = self::determineDescriptionStrategy($cleanedContent, $useFirstParagraph);
        
        $description = match($strategy) {
            'first_paragraph' => self::extractFirstParagraph($cleanedContent),
            'key_sentences' => self::extractKeySentences($cleanedContent, $targetKeywords),
            'summary' => self::generateIntelligentSummary($cleanedContent, $targetKeywords),
            default => self::extractFirstParagraph($cleanedContent)
        };

        // Enhance description with target keywords naturally
        $description = self::enhanceDescriptionWithKeywords($description, $targetKeywords);

        // Add call-to-action if provided
        if (!empty($callToAction)) {
            $description = self::addCallToAction($description, $callToAction, $maxLength);
        }

        // Add relevant emoji for engagement (if enabled)
        if ($includeEmoji) {
            $description = self::addRelevantEmoji($description);
        }

        return self::truncateSmart($description, $maxLength);
    }

    /**
     * Generate semantic keywords with LSI analysis
     */
    public static function generateSemanticKeywords(
        string $title,
        string $content = '',
        int $limit = self::DEFAULT_KEYWORD_LIMIT,
        array $targetKeywords = [],
        string $language = 'en'
    ): string {
        $textSources = [self::cleanText($title)];
        if (!empty($content)) {
            $textSources[] = self::cleanText($content);
        }

        $combinedText = implode(' ', $textSources);
        
        // Extract primary keywords
        $primaryKeywords = self::extractScoredKeywords($combinedText, $limit * 2);
        
        // Generate semantic variations and related terms
        $semanticKeywords = self::generateSemanticVariations($primaryKeywords, $language);
        
        // Merge with target keywords (higher priority)
        $allKeywords = array_unique(array_merge(
            $targetKeywords,
            array_keys($semanticKeywords)
        ));

        // Score and rank final keywords
        $finalKeywords = self::scoreAndRankKeywords($allKeywords, $combinedText, $limit);

        return implode(', ', $finalKeywords);
    }

    /**
     * Generate comprehensive meta tags with modern SEO features
     */
    public static function generateAdvancedMetaTags(array $options): string
    {
        $tags = [];

        // Validate required options
        self::validateMetaOptions($options);

        // Core meta tags
        $tags[] = self::generateTitleTag($options['title']);
        
        if (!empty($options['description'])) {
            $tags[] = self::generateDescriptionTag($options['description']);
        }

        // Robots meta tag with advanced directives
        $robotsDirectives = $options['robots'] ?? ['index', 'follow'];
        $tags[] = self::generateRobotsTag($robotsDirectives);

        // Canonical URL
        if (!empty($options['canonical'])) {
            $tags[] = self::generateCanonicalTag($options['canonical']);
        }

        // Hreflang tags for internationalization
        if (!empty($options['hreflang'])) {
            $tags = array_merge($tags, self::generateHreflangTags($options['hreflang']));
        }

        // Keywords (still useful for some search engines)
        if (!empty($options['keywords'])) {
            $tags[] = self::generateKeywordsTag($options['keywords']);
        }

        // Author and publisher information
        if (!empty($options['author'])) {
            $tags[] = self::generateMetaTag('author', $options['author']);
        }

        // Content freshness signals
        if (!empty($options['published_time'])) {
            $tags[] = self::generateMetaTag('article:published_time', $options['published_time']);
        }
        if (!empty($options['modified_time'])) {
            $tags[] = self::generateMetaTag('article:modified_time', $options['modified_time']);
        }

        // Open Graph tags
        if (!empty($options['og'])) {
            $tags = array_merge($tags, self::generateEnhancedOpenGraphTags($options['og']));
        }

        // Twitter Card tags
        if (!empty($options['twitter'])) {
            $tags = array_merge($tags, self::generateEnhancedTwitterCardTags($options['twitter']));
        }

        // JSON-LD structured data
        if (!empty($options['schema'])) {
            $tags[] = self::generateJsonLdSchema($options['schema']);
        }

        // Preload critical resources
        if (!empty($options['preload'])) {
            $tags = array_merge($tags, self::generatePreloadTags($options['preload']));
        }

        // Security and performance headers
        $tags = array_merge($tags, self::generateSecurityTags($options));

        return implode("\n", $tags);
    }

    /**
     * Generate JSON-LD structured data
     */
    public static function generateJsonLdSchema(array $schemaData): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $schemaData['type'] ?? 'WebPage'
        ];

        // Merge provided schema data
        $schema = array_merge($schema, $schemaData);

        // Ensure required fields based on type
        $schema = self::ensureRequiredSchemaFields($schema);

        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Generate breadcrumb navigation
     */
    public static function generateBreadcrumbSchema(array $breadcrumbs): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        foreach ($breadcrumbs as $index => $breadcrumb) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'] ?? null
            ];
        }

        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Generate enhanced Open Graph tags with advanced features
     */
    public static function generateEnhancedOpenGraphTags(array $ogData): array
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
            'image:alt' => '',
            'image:width' => self::OG_IMAGE_WIDTH,
            'image:height' => self::OG_IMAGE_HEIGHT,
            'image:type' => 'image/png',
            'locale' => 'en_US',
            'updated_time' => '',
            'see_also' => []
        ];

        $ogData = array_merge($defaults, $ogData);

        // Generate basic OG tags
        foreach ($ogData as $property => $content) {
            if (!empty($content) && !is_array($content)) {
                $prop = strpos($property, ':') !== false ? "og:$property" : "og:$property";
                $tags[] = self::generateMetaTag($prop, (string)$content, 'property');
            }
        }

        // Handle array properties (like see_also)
        if (!empty($ogData['see_also']) && is_array($ogData['see_also'])) {
            foreach ($ogData['see_also'] as $url) {
                $tags[] = self::generateMetaTag('og:see_also', $url, 'property');
            }
        }

        // Add Facebook-specific tags
        if (!empty($ogData['fb_app_id'])) {
            $tags[] = self::generateMetaTag('fb:app_id', $ogData['fb_app_id'], 'property');
        }

        return $tags;
    }

    /**
     * Generate enhanced Twitter Card tags
     */
    public static function generateEnhancedTwitterCardTags(array $twitterData): array
    {
        $tags = [];
        $defaults = [
            'card' => 'summary_large_image',
            'site' => '',
            'creator' => '',
            'title' => '',
            'description' => '',
            'image' => '',
            'image:alt' => '',
            'domain' => '',
            'data1' => '',
            'label1' => '',
            'data2' => '',
            'label2' => ''
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
     * Generate hreflang tags for international SEO
     */
    public static function generateHreflangTags(array $hreflangData): array
    {
        $tags = [];
        
        foreach ($hreflangData as $langCode => $url) {
            $hreflang = htmlspecialchars($langCode, ENT_QUOTES, 'UTF-8');
            $href = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $tags[] = "<link rel=\"alternate\" hreflang=\"{$hreflang}\" href=\"{$href}\">";
        }

        return $tags;
    }

    /**
     * Generate preload resource hints
     */
    public static function generatePreloadTags(array $resources): array
    {
        $tags = [];
        
        foreach ($resources as $resource) {
            $href = htmlspecialchars($resource['href'], ENT_QUOTES, 'UTF-8');
            $as = htmlspecialchars($resource['as'] ?? 'script', ENT_QUOTES, 'UTF-8');
            $type = !empty($resource['type']) ? " type=\"{$resource['type']}\"" : '';
            $crossorigin = !empty($resource['crossorigin']) ? " crossorigin" : '';
            
            $tags[] = "<link rel=\"preload\" href=\"{$href}\" as=\"{$as}\"{$type}{$crossorigin}>";
        }

        return $tags;
    }

    /**
     * Generate security-related meta tags
     */
    public static function generateSecurityTags(array $options): array
    {
        $tags = [];

        // Content Security Policy
        if (!empty($options['csp'])) {
            $tags[] = self::generateMetaTag('Content-Security-Policy', $options['csp'], 'http-equiv');
        }

        // Referrer Policy
        $referrerPolicy = $options['referrer_policy'] ?? 'strict-origin-when-cross-origin';
        $tags[] = self::generateMetaTag('referrer', $referrerPolicy);

        // Theme color for mobile browsers
        if (!empty($options['theme_color'])) {
            $tags[] = self::generateMetaTag('theme-color', $options['theme_color']);
        }

        // Viewport meta tag for mobile optimization
        $viewport = $options['viewport'] ?? 'width=device-width, initial-scale=1.0';
        $tags[] = self::generateMetaTag('viewport', $viewport);

        return $tags;
    }

    /**
     * Extract scored keywords with advanced NLP techniques
     */
    protected static function extractScoredKeywords(string $text, int $limit): array
    {
        $text = self::cleanText($text);
        $words = preg_split('/\s+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        
        // Get enhanced stop words
        $stopWords = self::getEnhancedStopWords();
        
        // Filter and process words
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) >= self::MIN_WORD_LENGTH &&
                   strlen($word) <= self::MAX_WORD_LENGTH &&
                   !in_array($word, $stopWords) &&
                   !is_numeric($word) &&
                   !preg_match('/[^a-z\-]/', $word);
        });

        // Count frequencies
        $wordCount = array_count_values($filteredWords);
        
        // Extract phrases (2-3 word combinations)
        $phrases = self::extractPhrases($words, $stopWords);
        
        // Score keywords using TF-IDF-like approach
        $scoredKeywords = self::scoreKeywords($wordCount, $phrases, $text);
        
        arsort($scoredKeywords);
        
        return array_slice($scoredKeywords, 0, $limit, true);
    }

    /**
     * Extract meaningful phrases from text
     */
    protected static function extractPhrases(array $words, array $stopWords): array
    {
        $phrases = [];
        $wordCount = count($words);
        
        // Extract 2-word phrases
        for ($i = 0; $i < $wordCount - 1; $i++) {
            if (!in_array($words[$i], $stopWords) && !in_array($words[$i + 1], $stopWords)) {
                $phrase = $words[$i] . ' ' . $words[$i + 1];
                $phrases[$phrase] = ($phrases[$phrase] ?? 0) + 1;
            }
        }
        
        // Extract 3-word phrases
        for ($i = 0; $i < $wordCount - 2; $i++) {
            if (!in_array($words[$i], $stopWords) && 
                !in_array($words[$i + 1], $stopWords) && 
                !in_array($words[$i + 2], $stopWords)) {
                $phrase = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
                $phrases[$phrase] = ($phrases[$phrase] ?? 0) + 1;
            }
        }
        
        return $phrases;
    }

    /**
     * Score keywords using multiple factors
     */
    protected static function scoreKeywords(array $wordCount, array $phrases, string $text): array
    {
        $scoredKeywords = [];
        $textLength = strlen($text);
        
        // Score individual words
        foreach ($wordCount as $word => $count) {
            $score = $count;
            
            // Position bonus (words appearing early get higher scores)
            $firstPosition = strpos($text, $word);
            if ($firstPosition !== false && $firstPosition < ($textLength * 0.2)) {
                $score *= 1.5;
            }
            
            // Length bonus (prefer longer meaningful words)
            if (strlen($word) > 6) {
                $score *= 1.2;
            }
            
            $scoredKeywords[$word] = $score;
        }
        
        // Score phrases (give them higher priority)
        foreach ($phrases as $phrase => $count) {
            if ($count > 1) { // Only include phrases that appear multiple times
                $scoredKeywords[$phrase] = $count * 2; // Phrases get 2x multiplier
            }
        }
        
        return $scoredKeywords;
    }

    /**
     * Generate semantic keyword variations
     */
    protected static function generateSemanticVariations(array $keywords, string $language): array
    {
        $variations = [];
        
        foreach ($keywords as $keyword => $score) {
            $variations[$keyword] = $score;
            
            // Add stemmed variations (simple English stemming)
            if ($language === 'en') {
                $stemmed = self::simpleStem($keyword);
                if ($stemmed !== $keyword) {
                    $variations[$stemmed] = $score * 0.8;
                }
            }
            
            // Add plurals and singulars
            $variations = array_merge($variations, self::generatePluralSingularVariations($keyword, $score));
        }
        
        return $variations;
    }

    /**
     * Simple English word stemming
     */
    protected static function simpleStem(string $word): string
    {
        // Simple suffix removal rules
        $suffixes = ['ing', 'ed', 'er', 'est', 'ly', 'tion', 'sion', 'ness'];
        
        foreach ($suffixes as $suffix) {
            if (str_ends_with($word, $suffix) && strlen($word) > strlen($suffix) + 3) {
                return substr($word, 0, -strlen($suffix));
            }
        }
        
        return $word;
    }

    /**
     * Generate plural/singular variations
     */
    protected static function generatePluralSingularVariations(string $word, float $score): array
    {
        $variations = [];
        
        // Simple pluralization rules
        if (str_ends_with($word, 's') && strlen($word) > 3) {
            // Likely plural, add singular
            $singular = rtrim($word, 's');
            $variations[$singular] = $score * 0.9;
        } else {
            // Likely singular, add plural
            $plural = $word . 's';
            $variations[$plural] = $score * 0.9;
        }
        
        return $variations;
    }

    /**
     * Enhanced stop words list
     */
    protected static function getEnhancedStopWords(): array
    {
        return [
            // Basic English stop words
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'been', 'by', 'for', 'from', 'has', 'he',
            'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'will', 'with',
            'would', 'could', 'should', 'have', 'had', 'this', 'these', 'they', 'them', 'their',
            'there', 'where', 'when', 'what', 'which', 'who', 'why', 'how', 'can', 'may', 'might',
            
            // SEO and web-specific stop words
            'page', 'website', 'site', 'web', 'online', 'internet', 'click', 'here', 'read', 'more',
            'view', 'see', 'get', 'find', 'search', 'buy', 'now', 'today', 'free', 'best', 'top',
            'new', 'latest', 'popular', 'trending', 'hot', 'cool', 'awesome', 'great', 'good',
            
            // Content-specific stop words
            'article', 'post', 'blog', 'content', 'text', 'word', 'words', 'sentence', 'paragraph',
            'section', 'chapter', 'title', 'heading', 'description', 'summary', 'overview',
            
            // Navigation and UI stop words
            'home', 'about', 'contact', 'privacy', 'terms', 'policy', 'help', 'support', 'faq',
            'login', 'register', 'signup', 'subscribe', 'newsletter', 'email', 'phone', 'address'
        ];
    }

    /**
     * Build title with brand positioning
     */
    protected static function buildTitleWithBrand(
        string $title,
        string $siteName,
        string $separator,
        string $brandPosition,
        array $keywords,
        bool $includeSiteName
    ): string {
        $parts = [];
        
        // Add brand at start if requested
        if ($brandPosition === 'start' && $includeSiteName && !empty($siteName)) {
            $parts[] = $siteName;
        }
        
        // Add main title
        $parts[] = $title;
        
        // Add keywords
        foreach ($keywords as $keyword) {
            $parts[] = $keyword;
        }
        
        // Add brand at end if requested
        if ($brandPosition === 'end' && $includeSiteName && !empty($siteName)) {
            $parts[] = $siteName;
        }
        
        return implode(" $separator ", array_filter($parts));
    }

    /**
     * Optimize title for click-through rate
     */
    protected static function optimizeTitleForCTR(string $title): string
    {
        // Add power words if not present
        $powerWords = ['Ultimate', 'Complete', 'Essential', 'Proven', 'Expert', 'Advanced'];
        $hasNumber = preg_match('/\d+/', $title);
        $hasPowerWord = false;
        
        foreach ($powerWords as $word) {
            if (stripos($title, $word) !== false) {
                $hasPowerWord = true;
                break;
            }
        }
        
        // If no number or power word, consider adding year for freshness
        if (!$hasNumber && !$hasPowerWord) {
            $currentYear = date('Y');
            if (stripos($title, $currentYear) === false) {
                $title = $title . ' (' . $currentYear . ')';
            }
        }
        
        return $title;
    }

    /**
     * Determine best description extraction strategy
     */
    protected static function determineDescriptionStrategy(string $content, bool $useFirstParagraph): string
    {
        $contentLength = strlen($content);
        
        if ($useFirstParagraph) {
            return 'first_paragraph';
        }
        
        if ($contentLength < self::MIN_CONTENT_LENGTH) {
            return 'first_paragraph';
        }
        
        if ($contentLength > self::IDEAL_CONTENT_LENGTH) {
            return 'key_sentences';
        }
        
        return 'summary';
    }

    /**
     * Extract key sentences based on target keywords
     */
    protected static function extractKeySentences(string $content, array $targetKeywords): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $scoredSentences = [];
        
        foreach ($sentences as $i => $sentence) {
            $score = 0;
            
            // Base score from position (earlier sentences get higher scores)
            $score += (count($sentences) - $i) / count($sentences) * 10;
            
            // Keyword density score
            foreach ($targetKeywords as $keyword) {
                if (self::containsWord($sentence, $keyword)) {
                    $score += 20;
                }
            }
            
            // Sentence length score (prefer medium-length sentences)
            $length = strlen($sentence);
            if ($length > 50 && $length < 200) {
                $score += 5;
            }
            
            $scoredSentences[$i] = $score;
        }
        
        // Get top 2 sentences
        arsort($scoredSentences);
        $topIndices = array_keys(array_slice($scoredSentences, 0, 2));
        sort($topIndices);
        
        $result = '';
        foreach ($topIndices as $index) {
            $result .= trim($sentences[$index]) . ' ';
        }
        
        return trim($result);
    }

    /**
     * Generate intelligent summary using extractive summarization
     */
    protected static function generateIntelligentSummary(string $content, array $targetKeywords): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($sentences)) {
            return '';
        }
        
        $scoredSentences = [];
        $totalSentences = count($sentences);
        
        foreach ($sentences as $i => $sentence) {
            $score = 0;
            
            // Position scoring (intro and conclusion are important)
            if ($i < $totalSentences * 0.2) { // First 20%
                $score += 15;
            } elseif ($i > $totalSentences * 0.8) { // Last 20%
                $score += 10;
            }
            
            // Length scoring (prefer substantial sentences)
            $length = strlen($sentence);
            if ($length > 30 && $length < 150) {
                $score += 8;
            }
            
            // Keyword presence scoring
            foreach ($targetKeywords as $keyword) {
                if (self::containsWord($sentence, $keyword)) {
                    $score += 25;
                }
            }
            
            // Sentence structure scoring (prefer sentences with specific patterns)
            if (preg_match('/\b(because|since|due to|as a result|therefore|thus|however|moreover|furthermore)\b/i', $sentence)) {
                $score += 5; // Explanatory sentences
            }
            
            $scoredSentences[$i] = $score;
        }
        
        // Select best sentences for summary
        arsort($scoredSentences);
        $selectedIndices = array_keys(array_slice($scoredSentences, 0, 3));
        sort($selectedIndices);
        
        $summary = '';
        foreach ($selectedIndices as $index) {
            $summary .= trim($sentences[$index]) . ' ';
        }
        
        return trim($summary);
    }

    /**
     * Enhance description with target keywords naturally
     */
    protected static function enhanceDescriptionWithKeywords(string $description, array $targetKeywords): string
    {
        if (empty($targetKeywords)) {
            return $description;
        }
        
        // Find keywords not already in description
        $missingKeywords = [];
        foreach ($targetKeywords as $keyword) {
            if (!self::containsWord($description, $keyword)) {
                $missingKeywords[] = $keyword;
            }
        }
        
        // Add one missing keyword naturally if there's space
        if (!empty($missingKeywords) && strlen($description) < 120) {
            $keywordToAdd = $missingKeywords[0];
            $description = "Learn about " . $keywordToAdd . ". " . $description;
        }
        
        return $description;
    }

    /**
     * Add call-to-action to description
     */
    protected static function addCallToAction(string $description, string $cta, int $maxLength): string
    {
        $ctaLength = strlen($cta) + 1; // +1 for space
        
        if (strlen($description) + $ctaLength <= $maxLength) {
            return $description . ' ' . $cta;
        }
        
        // Truncate description to make room for CTA
        $availableLength = $maxLength - $ctaLength;
        $truncatedDescription = self::truncateSmart($description, $availableLength);
        
        return $truncatedDescription . ' ' . $cta;
    }

    /**
     * Add relevant emoji to description for engagement
     */
    protected static function addRelevantEmoji(string $description): string
    {
        // Map keywords to relevant emojis
        $emojiMap = [
            'technology' => '💻',
            'business' => '💼',
            'marketing' => '📈',
            'design' => '🎨',
            'food' => '🍽️',
            'travel' => '✈️',
            'health' => '💪',
            'education' => '📚',
            'finance' => '💰',
            'sports' => '⚽',
            'music' => '🎵',
            'photography' => '📸',
            'fashion' => '👗',
            'coding' => '💻',
            'development' => '🚀',
            'science' => '🔬',
            'nature' => '🌿'
        ];
        
        foreach ($emojiMap as $keyword => $emoji) {
            if (self::containsWord(strtolower($description), $keyword)) {
                return $emoji . ' ' . $description;
            }
        }
        
        return $description;
    }

    /**
     * Score and rank final keywords
     */
    protected static function scoreAndRankKeywords(array $keywords, string $content, int $limit): array
    {
        $scoredKeywords = [];
        $contentLower = strtolower($content);
        
        foreach ($keywords as $keyword) {
            $score = 0;
            $keywordLower = strtolower($keyword);
            
            // Frequency score
            $frequency = substr_count($contentLower, $keywordLower);
            $score += $frequency * 10;
            
            // Position score (earlier appearance = higher score)
            $firstPos = strpos($contentLower, $keywordLower);
            if ($firstPos !== false) {
                $positionScore = 1 - ($firstPos / strlen($contentLower));
                $score += $positionScore * 15;
            }
            
            // Length score (prefer longer, more specific keywords)
            $wordCount = str_word_count($keyword);
            if ($wordCount > 1) {
                $score += $wordCount * 5; // Multi-word phrases get bonus
            }
            
            $scoredKeywords[$keyword] = $score;
        }
        
        arsort($scoredKeywords);
        return array_slice(array_keys($scoredKeywords), 0, $limit);
    }

    /**
     * Validate meta options
     */
    protected static function validateMetaOptions(array $options): void
    {
        if (empty($options['title'])) {
            throw new \InvalidArgumentException('Title is required for meta tags');
        }
        
        if (!empty($options['og']) && empty($options['og']['image'])) {
            throw new \InvalidArgumentException('Open Graph image is required when using OG tags');
        }
    }

    /**
     * Ensure required schema fields based on type
     */
    protected static function ensureRequiredSchemaFields(array $schema): array
    {
        $type = $schema['@type'] ?? 'WebPage';
        
        switch ($type) {
            case 'Article':
                $schema['headline'] = $schema['headline'] ?? $schema['name'] ?? '';
                $schema['author'] = $schema['author'] ?? ['@type' => 'Person', 'name' => 'Anonymous'];
                $schema['publisher'] = $schema['publisher'] ?? ['@type' => 'Organization', 'name' => ''];
                $schema['datePublished'] = $schema['datePublished'] ?? date('c');
                break;
                
            case 'Product':
                $schema['offers'] = $schema['offers'] ?? ['@type' => 'Offer', 'availability' => 'InStock'];
                break;
                
            case 'LocalBusiness':
                $schema['address'] = $schema['address'] ?? ['@type' => 'PostalAddress'];
                break;
        }
        
        return $schema;
    }

    /**
     * Generate robots meta tag with advanced directives
     */
    protected static function generateRobotsTag(array $directives): string
    {
        $validDirectives = [
            'index', 'noindex', 'follow', 'nofollow', 'noarchive', 'nosnippet',
            'noimageindex', 'notranslate', 'max-snippet', 'max-image-preview',
            'max-video-preview'
        ];
        
        $filteredDirectives = array_intersect($directives, $validDirectives);
        $robotsContent = implode(', ', $filteredDirectives);
        
        return self::generateMetaTag('robots', $robotsContent);
    }

    /**
     * Generate FAQ Schema markup
     */
    public static function generateFAQSchema(array $faqs): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => []
        ];
        
        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }
        
        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Generate Organization Schema markup
     */
    public static function generateOrganizationSchema(array $orgData): string
    {
        $defaults = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => '',
            'url' => '',
            'logo' => '',
            'sameAs' => [],
            'contactPoint' => []
        ];
        
        $schema = array_merge($defaults, $orgData);
        
        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Generate WebSite Schema with SearchAction
     */
    public static function generateWebsiteSchema(array $siteData): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteData['name'],
            'url' => $siteData['url']
        ];
        
        // Add search action if search URL is provided
        if (!empty($siteData['search_url'])) {
            $schema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $siteData['search_url'] . '?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ];
        }
        
        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Analyze content readability and SEO factors
     */
    public static function analyzeContentSEO(string $content, array $targetKeywords = []): array
    {
        $analysis = [];
        $cleanContent = self::cleanText($content);
        
        // Basic metrics
        $analysis['word_count'] = str_word_count($cleanContent);
        $analysis['character_count'] = strlen($cleanContent);
        $analysis['paragraph_count'] = count(array_filter(explode("\n\n", $content)));
        $analysis['sentence_count'] = preg_match_all('/[.!?]+/', $cleanContent);
        
        // Readability metrics
        $analysis['avg_sentence_length'] = $analysis['sentence_count'] > 0 
            ? round($analysis['word_count'] / $analysis['sentence_count'], 1) 
            : 0;
            
        $analysis['readability_score'] = self::calculateFleschScore($cleanContent);
        $analysis['readability_grade'] = self::getReadabilityGrade($analysis['readability_score']);
        
        // SEO metrics
        $analysis['keyword_density'] = [];
        foreach ($targetKeywords as $keyword) {
            $count = substr_count(strtolower($cleanContent), strtolower($keyword));
            $density = $analysis['word_count'] > 0 ? ($count / $analysis['word_count']) * 100 : 0;
            $analysis['keyword_density'][$keyword] = round($density, 2);
        }
        
        // Content structure analysis
        $analysis['has_headings'] = preg_match('/<h[1-6]/', $content) > 0;
        $analysis['has_lists'] = preg_match('/<[uo]l>/', $content) > 0;
        $analysis['has_images'] = preg_match('/<img/', $content) > 0;
        
        // SEO recommendations
        $analysis['recommendations'] = self::generateSEORecommendations($analysis, $targetKeywords);
        
        return $analysis;
    }

    /**
     * Calculate Flesch Reading Ease Score
     */
    protected static function calculateFleschScore(string $text): float
    {
        $sentences = preg_match_all('/[.!?]+/', $text);
        $words = str_word_count($text);
        $syllables = self::countSyllables($text);
        
        if ($sentences == 0 || $words == 0) {
            return 0;
        }
        
        $avgSentenceLength = $words / $sentences;
        $avgSyllablesPerWord = $syllables / $words;
        
        $score = 206.835 - (1.015 * $avgSentenceLength) - (84.6 * $avgSyllablesPerWord);
        
        return max(0, min(100, round($score, 1)));
    }

    /**
     * Count syllables in text (approximate)
     */
    protected static function countSyllables(string $text): int
    {
        $words = str_word_count($text, 1);
        $syllableCount = 0;
        
        foreach ($words as $word) {
            $word = strtolower($word);
            $vowels = preg_match_all('/[aeiouy]/', $word);
            $syllableCount += max(1, $vowels); // Minimum 1 syllable per word
        }
        
        return $syllableCount;
    }

    /**
     * Get readability grade from Flesch score
     */
    protected static function getReadabilityGrade(float $score): string
    {
        if ($score >= 90) return 'Very Easy';
        if ($score >= 80) return 'Easy';
        if ($score >= 70) return 'Fairly Easy';
        if ($score >= 60) return 'Standard';
        if ($score >= 50) return 'Fairly Difficult';
        if ($score >= 30) return 'Difficult';
        return 'Very Difficult';
    }

    /**
     * Generate SEO recommendations based on analysis
     */
    protected static function generateSEORecommendations(array $analysis, array $targetKeywords): array
    {
        $recommendations = [];
        
        // Word count recommendations
        if ($analysis['word_count'] < 300) {
            $recommendations[] = 'Content is too short. Aim for at least 300 words for better SEO.';
        } elseif ($analysis['word_count'] > 3000) {
            $recommendations[] = 'Content is very long. Consider breaking it into multiple pages.';
        }
        
        // Readability recommendations
        if ($analysis['readability_score'] < 50) {
            $recommendations[] = 'Content is difficult to read. Use shorter sentences and simpler words.';
        }
        
        if ($analysis['avg_sentence_length'] > 25) {
            $recommendations[] = 'Average sentence length is too long. Aim for 15-20 words per sentence.';
        }
        
        // Keyword density recommendations
        foreach ($analysis['keyword_density'] as $keyword => $density) {
            if ($density < 0.5) {
                $recommendations[] = "Keyword '$keyword' density is too low. Consider using it more naturally.";
            } elseif ($density > 3) {
                $recommendations[] = "Keyword '$keyword' density is too high. Avoid keyword stuffing.";
            }
        }
        
        // Structure recommendations
        if (!$analysis['has_headings']) {
            $recommendations[] = 'Add headings (H1-H6) to improve content structure and readability.';
        }
        
        if (!$analysis['has_lists']) {
            $recommendations[] = 'Consider adding lists to make content more scannable.';
        }
        
        if (!$analysis['has_images']) {
            $recommendations[] = 'Add relevant images to improve user engagement.';
        }
        
        return $recommendations;
    }

    /**
     * Generate comprehensive canonical URL with better handling
     */
    public static function generateCanonicalUrl(
        string $baseUrl,
        ?string $additionalPath = null,
        array $queryParams = [],
        bool $https = true,
        bool $www = null,
        bool $trailingSlash = false
    ): string {
        // Parse and normalize base URL
        $parsedUrl = parse_url($baseUrl);
        
        if (!$parsedUrl) {
            throw new \InvalidArgumentException('Invalid base URL provided');
        }
        
        // Build scheme
        $scheme = $https ? 'https' : ($parsedUrl['scheme'] ?? 'https');
        
        // Handle www preference
        $host = $parsedUrl['host'] ?? '';
        if ($www === true && !str_starts_with($host, 'www.')) {
            $host = 'www.' . $host;
        } elseif ($www === false && str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }
        
        // Build path
        $path = rtrim($parsedUrl['path'] ?? '', '/');
        if ($additionalPath) {
            $path .= '/' . trim($additionalPath, '/');
        }
        
        // Add trailing slash if requested
        if ($trailingSlash && !empty($path)) {
            $path .= '/';
        }
        
        // Build URL
        $url = $scheme . '://' . $host . $path;
        
        // Add query parameters
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        }
        
        return $url;
    }

    /**
     * Enhanced smart truncation with better word boundary detection
     */
    protected static function truncateSmart(string $text, int $maxLength): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        // Find the best truncation point
        $truncated = mb_substr($text, 0, $maxLength);
        
        // Look for sentence boundaries first
        $sentenceEnd = mb_strrpos($truncated, '.');
        if ($sentenceEnd && $sentenceEnd > ($maxLength * 0.7)) {
            return mb_substr($truncated, 0, $sentenceEnd + 1);
        }
        
        // Then look for word boundaries
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace && $lastSpace > ($maxLength * 0.8)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        // Clean up trailing punctuation
        $truncated = rtrim($truncated, '.,;:!?-');

        return $truncated . '...';
    }

    /**
     * Enhanced word containment check with fuzzy matching
     */
    protected static function containsWord(string $text, string $word): bool
    {
        // Exact word match
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
            return true;
        }
        
        // Check for partial matches (useful for stemmed words)
        $wordStem = self::simpleStem(strtolower($word));
        if ($wordStem !== strtolower($word)) {
            return preg_match('/\b' . preg_quote($wordStem, '/') . '/i', $text) === 1;
        }
        
        return false;
    }

    /**
     * Enhanced text cleaning with better HTML handling
     */
    protected static function cleanText(string $text): string
    {
        // Preserve line breaks temporarily
        $text = str_replace(["\r\n", "\n", "\r"], '|||LINEBREAK|||', $text);
        
        // Remove HTML tags but preserve content
        $text = strip_tags($text);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Restore line breaks and normalize
        $text = str_replace('|||LINEBREAK|||', "\n", $text);
        
        // Normalize whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);
        
        // Remove special characters but preserve basic punctuation
        $text = preg_replace('/[^\w\s\-.,;:!?\'"\(\)\[\]\/]/', '', $text);

        return trim($text);
    }

    /**
     * Helper method to generate meta tags with better escaping
     */
    private static function generateMetaTag(
        string $name,
        string $content,
        string $attribute = 'name'
    ): string {
        $name = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8');
        
        return "<meta {$attribute}=\"{$name}\" content=\"{$content}\">";
    }

    /**
     * Generate title tag with proper escaping
     */
    private static function generateTitleTag(string $title): string
    {
        return '<title>' . htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8') . '</title>';
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
     * Generate canonical link tag with validation
     */
    private static function generateCanonicalTag(string $url): string
    {
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid canonical URL provided');
        }
        
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return "<link rel=\"canonical\" href=\"{$url}\">";
    }
}