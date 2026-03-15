<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML → Markdown converter.
 *
 * Handles the elements WordPress / Gutenberg / Elementor commonly output:
 *   - Headings h1–h6
 *   - Bold, italic, inline code
 *   - Links and images
 *   - Ordered and unordered lists (nested)
 *   - Blockquotes
 *   - Fenced code blocks (pre > code)
 *   - Horizontal rules
 *   - Tables
 *   - Paragraphs and line breaks
 *
 * Uses PHP's DOMDocument so it handles real-world messy HTML safely
 * without regex soup.
 */

class MDEP_Converter {

    /** @var DOMDocument */
    private $dom;

    /**
     * Convert an HTML string to Markdown.
     *
     * @param string $html Raw HTML (post_content after apply_filters('the_content',...))
     * @return string Clean Markdown
     */
    public function convert( string $html ): string {
        if ( empty( trim( $html ) ) ) {
            return '';
        }

        // Suppress libxml warnings on messy HTML
        libxml_use_internal_errors( true );

        $this->dom = new DOMDocument( '1.0', 'UTF-8' );

        // Force UTF-8 so multibyte characters survive
        $this->dom->loadHTML(
            '<?xml encoding="UTF-8">' .
            '<html><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        // Strip <style> and <script> nodes before conversion so they never
        // appear in the Markdown output — keeps AI crawler output clean.
        $this->strip_tags_by_name( [ 'style', 'script' ] );

        $body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
        $md   = $body ? $this->convert_node( $body ) : '';

        // Normalise whitespace: strip lines containing only spaces/tabs,
        // then collapse any run of 3+ newlines to a single blank line.
        $md = preg_replace( '/^[ \t]+$/m', '', $md );
        $md = preg_replace( '/\n{3,}/', "\n\n", $md );

        return trim( $md ) . "\n";
    }


    // -------------------------------------------------------------------------
    // DOM pre-processing
    // -------------------------------------------------------------------------

    /**
     * Remove all elements matching the given tag names from the DOM before
     * conversion. Eliminates <style> and <script> blocks so their raw content
     * never leaks into the Markdown output.
     *
     * @param string[] $tag_names
     */
    private function strip_tags_by_name( array $tag_names ): void {
        foreach ( $tag_names as $tag ) {
            $nodes = $this->dom->getElementsByTagName( $tag );
            // Iterate in reverse — live NodeList shrinks as we remove nodes
            for ( $i = $nodes->length - 1; $i >= 0; $i-- ) {
                $node = $nodes->item( $i );
                if ( $node && $node->parentNode ) {
                    $node->parentNode->removeChild( $node );
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Node dispatcher
    // -------------------------------------------------------------------------

    private function convert_node( DOMNode $node ): string {
        if ( $node instanceof DOMText ) {
            return $this->convert_text( $node );
        }

        if ( ! ( $node instanceof DOMElement ) ) {
            return '';
        }

        $tag = strtolower( $node->nodeName );

        switch ( $tag ) {
            // Block elements
            case 'h1': return "\n\n# "      . $this->inner_md( $node ) . "\n\n";
            case 'h2': return "\n\n## "     . $this->inner_md( $node ) . "\n\n";
            case 'h3': return "\n\n### "    . $this->inner_md( $node ) . "\n\n";
            case 'h4': return "\n\n#### "   . $this->inner_md( $node ) . "\n\n";
            case 'h5': return "\n\n##### "  . $this->inner_md( $node ) . "\n\n";
            case 'h6': return "\n\n###### " . $this->inner_md( $node ) . "\n\n";

            case 'p':          return "\n\n" . $this->inner_md( $node ) . "\n\n";
            case 'br':         return "  \n";
            case 'hr':         return "\n\n---\n\n";
            case 'blockquote': return $this->convert_blockquote( $node );
            case 'pre':        return $this->convert_pre( $node );
            case 'ul':         return $this->convert_list( $node, false );
            case 'ol':         return $this->convert_list( $node, true );
            case 'li':         return $this->inner_md( $node ); // handled by convert_list
            case 'table':      return $this->convert_table( $node );

            // Inline elements
            case 'strong':
            case 'b':          return '**' . $this->inner_md( $node ) . '**';
            case 'em':
            case 'i':          return '_' . $this->inner_md( $node ) . '_';
            case 'code':       return '`' . $node->textContent . '`';
            case 'a':          return $this->convert_link( $node );
            case 'img':        return $this->convert_image( $node );
            case 'del':
            case 's':          return '~~' . $this->inner_md( $node ) . '~~';

            // Structural wrappers — just recurse into children
            case 'div':
            case 'section':
            case 'article':
            case 'main':
            case 'header':
            case 'footer':
            case 'aside':
            case 'figure':
            case 'figcaption':
            case 'span':
            case 'body':
            default:
                return $this->inner_md( $node );
        }
    }

    // -------------------------------------------------------------------------
    // Inline helpers
    // -------------------------------------------------------------------------

    /** Render all child nodes and concatenate. */
    private function inner_md( DOMNode $node ): string {
        $out = '';
        foreach ( $node->childNodes as $child ) {
            $out .= $this->convert_node( $child );
        }
        return $out;
    }

    /** Clean up text node content. */
    private function convert_text( DOMText $node ): string {
        $text = $node->nodeValue;
        // Decode HTML entities
        $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        // Collapse internal whitespace runs to single space
        $text = preg_replace( '/[ \t]+/', ' ', $text );
        return $text;
    }

    // -------------------------------------------------------------------------
    // Block element converters
    // -------------------------------------------------------------------------

    private function convert_blockquote( DOMElement $node ): string {
        $inner = trim( $this->inner_md( $node ) );
        $lines = explode( "\n", $inner );
        $quoted = implode( "\n", array_map( fn( $l ) => '> ' . $l, $lines ) );
        return "\n\n" . $quoted . "\n\n";
    }

    private function convert_pre( DOMElement $node ): string {
        // Detect language from <code class="language-php"> etc.
        $lang = '';
        $code_el = $node->getElementsByTagName( 'code' )->item( 0 );
        if ( $code_el ) {
            $class = $code_el->getAttribute( 'class' );
            if ( preg_match( '/language-(\S+)/', $class, $m ) ) {
                $lang = $m[1];
            }
            $text = $code_el->textContent;
        } else {
            $text = $node->textContent;
        }
        $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        return "\n\n```{$lang}\n" . rtrim( $text ) . "\n```\n\n";
    }

    private function convert_list( DOMElement $node, bool $ordered, int $depth = 0 ): string {
        $out    = "\n\n";
        $indent = str_repeat( '    ', $depth );
        $index  = 1;

        foreach ( $node->childNodes as $child ) {
            if ( ! ( $child instanceof DOMElement ) || strtolower( $child->nodeName ) !== 'li' ) {
                continue;
            }

            $prefix   = $ordered ? "{$index}. " : '- ';
            $li_parts = '';

            foreach ( $child->childNodes as $li_child ) {
                if ( $li_child instanceof DOMElement ) {
                    $tag = strtolower( $li_child->nodeName );
                    if ( $tag === 'ul' ) {
                        $li_parts .= $this->convert_list( $li_child, false, $depth + 1 );
                        continue;
                    }
                    if ( $tag === 'ol' ) {
                        $li_parts .= $this->convert_list( $li_child, true, $depth + 1 );
                        continue;
                    }
                }
                $li_parts .= $this->convert_node( $li_child );
            }

            $li_text = trim( $li_parts );
            $out    .= $indent . $prefix . $li_text . "\n";
            $index++;
        }

        return $out . ( $depth === 0 ? "\n" : '' );
    }

    private function convert_table( DOMElement $node ): string {
        $rows    = [];
        $headers = [];

        // Collect header row
        foreach ( $node->getElementsByTagName( 'th' ) as $th ) {
            $headers[] = trim( $this->inner_md( $th ) );
        }

        // Collect body rows
        foreach ( $node->getElementsByTagName( 'tr' ) as $tr ) {
            $cells = [];
            foreach ( $tr->childNodes as $cell ) {
                if ( $cell instanceof DOMElement && in_array( strtolower( $cell->nodeName ), [ 'td', 'th' ] ) ) {
                    $cells[] = trim( $this->inner_md( $cell ) );
                }
            }
            if ( ! empty( $cells ) ) {
                $rows[] = $cells;
            }
        }

        if ( empty( $rows ) ) {
            return '';
        }

        // Use first row as header if no <th> found
        if ( empty( $headers ) ) {
            $headers = array_shift( $rows );
        }

        $col_count = count( $headers );
        $md  = "\n\n| " . implode( ' | ', $headers ) . " |\n";
        $md .= '| ' . implode( ' | ', array_fill( 0, $col_count, '---' ) ) . " |\n";

        foreach ( $rows as $row ) {
            // Pad/trim to column count
            while ( count( $row ) < $col_count ) $row[] = '';
            $row = array_slice( $row, 0, $col_count );
            $md .= '| ' . implode( ' | ', $row ) . " |\n";
        }

        return $md . "\n";
    }

    // -------------------------------------------------------------------------
    // Inline element converters
    // -------------------------------------------------------------------------

    private function convert_link( DOMElement $node ): string {
        $href  = $node->getAttribute( 'href' );
        $title = $node->getAttribute( 'title' );
        $text  = $this->inner_md( $node );

        if ( empty( $text ) ) {
            $text = $href;
        }

        if ( $title ) {
            return "[{$text}]({$href} \"{$title}\")";
        }
        return "[{$text}]({$href})";
    }

    private function convert_image( DOMElement $node ): string {
        $src   = $node->getAttribute( 'src' );
        $alt   = $node->getAttribute( 'alt' );
        $title = $node->getAttribute( 'title' );

        if ( $title ) {
            return "![{$alt}]({$src} \"{$title}\")";
        }
        return "![{$alt}]({$src})";
    }
}
