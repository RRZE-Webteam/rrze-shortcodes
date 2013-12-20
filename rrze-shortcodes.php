<?php
/**
 * Plugin Name: RRZE-Shortcodes
 * Description: Shortcodes.
 * Version: 1.0
 * Author: RRZE-Webteam
 * Author URI: http://blogs.fau.de/webworking/
 * License: GPLv2 or later
 */

/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

add_action( 'plugins_loaded', array( 'RRZE_Shortcodes', 'init' ) );

register_activation_hook( __FILE__, array( 'RRZE_Shortcodes', 'activation' ) );

class RRZE_Shortcodes {

    const version = '1.0'; // Plugin-Version
    
    const option_name = '_rrze_shortcodes';

    const version_option_name = '_rrze_shortcodes_version';
    
    const textdomain = 'rrze-shortcodes';
    
    const php_version = '5.3'; // Minimal erforderliche PHP-Version
    
    const wp_version = '3.8'; // Minimal erforderliche WordPress-Version
    
    public static function init() {

        load_plugin_textdomain( self::textdomain, false, sprintf( '%s/languages/', dirname( plugin_basename( __FILE__ ) ) ) );
        
        add_action( 'init', array( __CLASS__, 'update_version' ) );
        
        add_action( 'template_redirect', array( __CLASS__, 'enqueue_styles' ) );
        
        add_action( 'admin_head-post-new.php', array( __CLASS__, 'add_post_new_help_tab'));        
        add_action( 'admin_head-post.php', array( __CLASS__, 'add_post_new_help_tab'));    
        
        add_shortcode( 'rss', array( __CLASS__, 'rss' ) );
                
	add_shortcode( 'latex', array( __CLASS__, 'latex' ) );
        
        add_shortcode( 'ytembed', array(__CLASS__, 'yt_embed' ));
        
     }

    public static function activation() {
        self::version_compare();
        
        update_option( self::version_option_name , self::version );
    }
        
    public static function version_compare() {
        $error = '';
        
        if ( version_compare( PHP_VERSION, self::php_version, '<' ) ) {
            $error = sprintf( __( 'Ihre PHP-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die PHP-Version %s.', self::textdomain ), PHP_VERSION, self::php_version );
        }

        if ( version_compare( $GLOBALS['wp_version'], self::wp_version, '<' ) ) {
            $error = sprintf( __( 'Ihre Wordpress-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die Wordpress-Version %s.', self::textdomain ), $GLOBALS['wp_version'], self::wp_version );
        }

        if( ! empty( $error ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ), false, true );
            wp_die( $error );
        }
        
    }
    
    public static function update_version() {
	if( get_option( self::version_option_name, null) != self::version )
            update_option( self::version_option_name , self::version );
    }
          
    public static function enqueue_styles() {
        wp_enqueue_style( 'shortcodes-all', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/css/all.css', array('dashicons'), '1.0', 'all' );        
    }
    
    private static function default_options() {
        if ( ! empty( $GLOBALS['content_width'] ) )
            $width = (int) $GLOBALS['content_width'];

        if ( empty( $width ) )
            $width = 500;

        $height = min( ceil( $width * 1.5 ), 1000 );
     
        $options = array(
            'embed_defaults' => array(
                'width' => $width,
                'height' => $height
            ),
        );       
        return $options;
    }

    public static function add_post_new_help_tab() {
    $screen = get_current_screen();
    $standard = __('Standardwert %s', self::textdomain);
    $possible = __('Mögliche Optionen: %s', self::textdomain);
    $example = __('Beispiel: <br />%s <br />', self::textdomain);
    $content_shortcodes = array(
        '<p>' . sprintf(__('Shortcodes sind kleine Codes, mit denen man ohne großen Aufwand nützliche Elemente im Textmodus in die Beiträge einbinden kann. Viele Shortcodes werden Ihnen bereits von WordPress zur Verfügung gestellt. Diese finden Sie hier: %s', self::textdomain), '<a href="http://en.support.wordpress.com/shortcodes/" target="_blank">http://en.support.wordpress.com/shortcodes/</a>') . '</p>',
        '<p><strong>' . __('Zusätzliche RRZE-Shortcodes', self::textdomain) . '</strong></p>',
        '<p><strong>latex</strong> - ' . __('LaTeX-Formatierungen in WordPress einbinden.', self::textdomain) . ' ' . sprintf($example, '[latex color="000000" background="00ff00" size="4"][/latex]') . '</p>',
        '<ol>',
        '<li><strong>color</strong> - ' . __('Vordergrundfarbe in Hexadezimal-Farbwert', self::textdomain) . '</li>',
        '<li><strong>background</strong> - ' . __('Hintergrundfarbe in Hexadezimal-Farbwert', self::textdomain) . '</li>',
        '<li><strong>size</strong> - ' . __('entspricht der LaTeX-Größe,', self::textdomain) . ' ' . sprintf($standard, '= 0') . '. '. sprintf($possible, '-4 (\tiny), -3 (\scriptsize), -2 (\footnotesize), -1 (\small), 0 (\normalsize (12pt)), 1 (\large), 2 (\Large), 3 (\LARGE), 4 (\huge)') . '</li>',        
        '</ol>',
        '<p><strong>rss</strong> - ' . __('RSS-Feeds einbinden.', self::textdomain) . ' ' . sprintf($example, '[rss url="http://blogs.fau.de/webworking/feed" show_description=1 show_date=1 date_format="j. F Y"]') . '</p>',
        '<ol>',
        '<li><strong>title</strong> - ' . __('Eigenen Titel für RSS-Feed angeben.', self::textdomain) . ' ' . sprintf($standard, '= ""') . '</li>',
        '<li><strong>url</strong> - ' . __('Adresse des RSS-Feeds.', self::textdomain) . ' ' . sprintf($standard, '= "http://blogs.fau.de/rrze/feed"') . '</li>',
        '<li><strong>items</strong> - ' . __('Anzahl der angezeigten Meldungen.', self::textdomain) . ' ' . sprintf($standard, '= "5"') . '</li>',
        '<li><strong>show_title</strong> - ' . __('Anzeige des Titels des RSS-Feeds.', self::textdomain) . ' ' . sprintf($standard, '= 0') . '</li>',
        '<li><strong>show_author</strong> - ' . __('Anzeige des Autors der Meldungen.', self::textdomain) . ' ' . sprintf($standard, '= 0') . '</li>',
        '<li><strong>show_source</strong> - ' . __('Anzeige der Quelle des RSS-Feeds.', self::textdomain) . ' ' . sprintf($standard, '= 0') . '</li>',
        '<li><strong>show_description</strong> - ' . __('Anzeige einer Kurzbeschreibung der Meldungen.', self::textdomain) . ' ' . sprintf($standard, '= 0') . '</li>',
        '<li><strong>max_description</strong> - ' . __('Maximal angezeigte Zeichen der Kurzbeschreibung.', self::textdomain) . ' ' . sprintf($standard, '= "25"') . '</li>',
        '<li><strong>show_date</strong> - ' . __('Anzeige des Datums der Meldungen.', self::textdomain) . ' ' . sprintf($standard, '= 0') . '</li>',
        '<li><strong>date_format</strong> - ' . __('Datumsformat. Standardwert siehe Einstellungen/Zeitformat.', self::textdomain) . '</li>',
        '</ol>',
        '<p><strong>ytembed</strong> - ' . __('YouTube-Videos ohne Cookies einbinden. Innerhalb des Shortcodes muss der Code zum YouTube-Video angegeben werden.', self::textdomain). ' ' . sprintf($example, '[ytembed align=middle width=300 norel=0]PTVrTEda4wk[/ytembed]') . '</p>',
        '<ol>',
        '<li><strong>align</strong> - ' . __('Ausrichtung des Videos.', self::textdomain) . ' ' . sprintf($standard, '= "left"') . '. '. sprintf($possible, 'left, middle, right') . '</li>',
        '<li><strong>cookie</strong> - ' . __('Anzeige des Videos unter Einbindung von Cookies.', self::textdomain) . ' ' . sprintf($standard, '= "no"') . '. '. sprintf($possible, 'yes, no') . '</li>',
        '<li><strong>norel</strong> - ' . __('Keine ähnlichen Videos am Ende anzeigen.', self::textdomain) . ' ' . sprintf($standard, '= 1') . '. '. sprintf($possible, '0, 1') . '</li>',     
        '<li><strong>yttext</strong> - ' . __('Link zu dem YouTube-Video unterhalb der Vorschau anzeigen.', self::textdomain) . ' ' . sprintf($standard, '= yes') .  '. ' . sprintf($possible, 'yes, no') . '</li>',
        '<li><strong>width</strong> - ' . __('Breite der YouTube-Vorschau.', self::textdomain) . ' ' . sprintf($standard, '= WordPress-Defaults') . '</li>',     
        '</ol>',
    );
       
   $help_tab_shortcodes = array(
        'id' => 'shortcodes',
        'title' => __('RRZE-Shortcodes', self::textdomain),
        'content' => implode(PHP_EOL, $content_shortcodes),
        );
    
   
   $screen->add_help_tab( $help_tab_shortcodes );   
   
   }    
    
    public static function rss( $atts ) {
        $atts = shortcode_atts( 
                array(
                    'title' => '',
                    'url' => 'http://blogs.fau.de/rrze/feed',
                    'items' => '5',
                    'max_description_words' => '25',
                    'date_format' => get_option( 'date_format' ),
                    'show_title'=> 0,
                    'show_description' => 0,
                    'show_author' => 0,
                    'show_date' => 0,
                    'show_source' => 0
                ), $atts );

        extract( $atts );
        
        include_once ABSPATH . WPINC . '/feed.php';

		while ( stristr( $url, 'http') != $url )
			$url = substr( $url, 1 );

		if ( empty( $url ) )
			return '';
        
		if ( in_array( untrailingslashit( $url ), array( site_url(), home_url() ) ) )
			return '';

		$rss = fetch_feed( $url );

		$desc = '';
		$link = '';

		if ( ! is_wp_error( $rss ) ) {
			$desc = esc_attr( strip_tags(@html_entity_decode( $rss->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) ) ) );
			if ( empty( $title ) )
				$title = esc_html( strip_tags( $rss->get_title() ) );
            
			$link = esc_url( strip_tags( $rss->get_permalink() ) );
			while ( stristr( $link, 'http' ) != $link )
				$link = substr( $link, 1 );
		}

        $html = '<div class="rss-shortcode">';
        
        $show_title = (int) $show_title;
        
        if( $show_title ) {
            if ( empty( $title ) )
                $title = empty( $desc ) ? __( 'Unbekannter Feed', self::textdomain ) : $desc;

            $title = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', $link, $desc, $title );
            
            $html .= sprintf( '<h4>%s</h4>', $title );
        }
        
        $html .= self::rss_output( $rss, $atts );
        
        $html .= '</div>';
        
        return $html;
    }

    private static function rss_output( $rss, $atts ) {
        extract( $atts );
        
        if ( is_wp_error( $rss ) ) {
            if ( is_admin() || current_user_can( 'manage_options' ) )
                return '<p>' . sprintf( __( '<strong>RSS-Fehler</strong>: %s', self::textdomain ), $rss->get_error_message() ) . '</p>';
            
            return '';
        }
        
        $items = (int) $items;
        if ( $items < 1 || 20 < $items )
            $items = 10;
        
        $show_description = (int) $show_description;
        $show_author = (int) $show_author;
        $show_date = (int) $show_date;
        
        $max_description_words = $max_description_words < 10 ? 55 : (int) $max_description_words;
        
        if ( ! $rss->get_item_quantity() ) {
            echo '<p>' . __( 'Ein Fehler ist aufgetreten, das Feed ist wahrscheinlich nicht vorhanden. Versuchen Sie es später erneut.', self::textdomain ) . '</p>';
            $rss->__destruct();
            unset( $rss );
            return;
        }
        
        $html = '<ul>';
        foreach ( $rss->get_items( 0, $items ) as $item ) {
            $link = $item->get_link();
            while ( stristr( $link, 'http' ) != $link )
                $link = substr( $link, 1 );
            
            $link = esc_url( strip_tags( $link ) );
            
            $title = esc_attr( strip_tags( $item->get_title() ) );
            if ( empty( $title ) )
                $title = __( 'Ohne Titel', self::textdomain );

            $desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) ) ) ) );
            $desc = wp_trim_words( $desc, $max_description_words );

            $desc = esc_html( $desc );

            if ( $show_description )
                $summary = sprintf( '<div class="rss-description">%s</div>', $desc );
            else
                $summary = '';

            $date = '';
            if ( $show_date ) {
                $date = $item->get_date( 'U' );
                if ( $date )
                    $date = sprintf( '<span class="rss-date">%s</span>', date_i18n( $date_format, $date ) );
            }

            $author = '';
            if ( $show_author ) {
                $author = $item->get_author();
                if ( is_object( $author ) ) {
                    $author = $author->get_name();
                    $author = sprintf( '<span class="rss-author">%s</span>', esc_html( strip_tags( $author ) ) );
                }
            }

            $source = '';
            if ( $show_source ) {
                $source = $item->get_item_tags( SIMPLEPIE_NAMESPACE_RSS_20, 'source' );
                if ( $source ) {
                    $source = $source[0]['data'];
                    $source = sprintf( '<span class="rss-source">%s</span>', esc_html( strip_tags( $source ) ) );
                }
            }
            
            if ( $link == '' )
                $html .= sprintf( '<li>%1$s%2$s%3$s%4$s%5$s</li>', $title, $date, $summary, $author, $source );               
            else
                $html .= sprintf( '<li><a href="%1$s" title="%2$s">%3$s</a>%4$s%5$s%6$s%7$s</li>', $link, $desc, $title, $date, $summary, $author, $source );
           
        }
        $html .= '</ul>';
        
        $rss->__destruct();
        unset($rss);
        
        return $html;    
        
    }
    
    public static function latex( $_atts, $latex ) {
	$atts = shortcode_atts( array(
		'size' => 0,
		'color' => '000000',
		'background' => 'ffffff',
	), $_atts );

	$latex = preg_replace( array( '#<br\s*/?>#i', '#</?p>#i' ), ' ', $latex );

	$latex = str_replace(
		array( '&lt;', '&gt;', '&quot;', '&#8220;', '&#8221;', '&#039;', '&#8125;', '&#8127;', '&#8217;', '&#038;', '&amp;', "\n", "\r", "\xa0", '&#8211;' ),
		array( '<',    '>',    '"',      '``',       "''",     "'",      "'",       "'",       "'",       '&',      '&',     ' ',  ' ',  ' ',    '-' ),
		$latex
	);

	$latex  = (string) $latex;
	$background = self::sanitize_hex( $atts['background'] );
	$color = self::sanitize_hex( $atts['color'] );
	$size = (int) $atts['size'];
        
        $url = sprintf( 'http://s.wordpress.com/latex.php?latex=%1$s&bg=%2$s&fg=%3$s&s=%4$s', rawurlencode( $latex ), $background, $color, $size );

	return sprintf( '<img src="%1$s" alt="%2$s" title="%2$s" class="latex-shortcode">', $url, $latex );
    }

    private static function sanitize_hex( $color ) {
	if ( 'transparent' == $color )
		return 'T';

	if ( 3 == strlen( $color ) )
		$color = $color[0] . $color[0] . $color[1] . $color[1]. $color[2] . $color[2];

	$color = substr( preg_replace( '/[^0-9a-f]/i', '', (string) $color ), 0, 6 );
	if ( 6 > $l = strlen( $color ) )
		$color .= str_repeat('0', 6 - $l );
        
	return $color;
    }    
    
    public static function yt_embed( $atts, $content = null ) {
        $defaults = self::default_options();
        extract( shortcode_atts( array(
            'align' => '',
            'width' => $defaults['embed_defaults']['width'],
            'cookie' => 'no',
            'norel' => 1,
            'yttext' => 'yes'
        ), $atts));
        $style = '';
        if ($align == "left") {
            $style = " ytleft";
        } elseif ($align == "right") {
            $style = " ytright";
        } elseif ($align == "middle") {
            $style = " ytmiddle";
        }
        $relvideo = '';
        if ($norel==1) {
            $relvideo = '?rel=0';
        }
        $height = $width*36/64;
        $url = '';


        if ($cookie == "no") {
            $url = 'https://www.youtube-nocookie.com/embed/' . $content;
        } elseif ($cookie == "yes") {
            $url = '//www.youtube.com/embed/' . $content;
        }
        $text = '';
        $yturl = 'http://www.youtube.com/watch?v=' . $content;
        $str = __('YouTube-Video', self::textdomain);
        if ($yttext == "yes") {
            $text = sprintf(
                    '<p>%2$s: <a href="%1$s">%1$s</a></p>',
                    $yturl,
                    $str
                    );
        }
        $embed = sprintf(
            '<div class="embed-youtube%5$s"><iframe src="%1$s%3$s" width="%2$spx" height="%4$spx" frameborder="0" scrolling="no" marginwidth="0" marginheight="0"></iframe>%6$s</div>',
            $url,
            $width,
            $relvideo,
            $height,
            $style,
            $text
            );
        return $embed;
    }
    
}
