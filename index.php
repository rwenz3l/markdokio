<?php

require 'vendor/autoload.php';

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extras\CommonMarkExtrasExtension;
use LitEmoji\LitEmoji;

const DOCUMENTS = __FILE__ . DIRECTORY_SEPARATOR . 'notes';

$f = $_GET['f'];
if ($f === null) {
    $f = 'README.md';
}
if (jail_check($f)) {
    echo '<header>';
    echo '<title>' . basename($f) . '</title>';
    echo '<link href="style/markdown.css" rel="stylesheet"</link>';
    echo '<link href="style/main.css" rel="stylesheet"</link>';
    echo '</header>';
    echo '<body>';
    echo '<div class="sidenav">';
    tree("notes");
    echo '</div>';
    echo '<div id="markdown">';
    render($f);
    echo '</div>';
} else {
    echo 'Path Traversal, huh?';
}
exit(0);

function tree($d)
{
    echo '<ul>';
    $files = preg_grep('/^([^.])/', scandir($d));
    foreach ($files as $f) {
        $p = $d . DIRECTORY_SEPARATOR . $f;
        if (is_dir($p)) {
            echo '<li">' . $f . '</li>';
            tree($p);
        } else {
            $url = urlencode($p);
            echo '<li><a href=/index.php?f=' . $url . '>' . $f . '</a></li>';
        }
    }
    echo '</ul>';
}

function render_v1($f)
{
    $parser = new ParsedownExtra();
    $md = file_get_contents($f);
    $md = LitEmoji::encodeUnicode($f);
    $html = $parser->text($md);
    echo $html;
}

function render($f)
{
    $environment = Environment::createCommonMarkEnvironment();
    $environment->addExtension(new CommonMarkExtrasExtension());
    $config = [
        'renderer' => [
            'block_separator' => "\n",
            'inner_separator' => "\n",
            'soft_break'      => "\n",
        ],
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisk' => true,
        'use_underscore' => true,
        'html_input' => 'escape',
        'allow_unsafe_links' => false,
        'max_nesting_level' => INF,
    ];
    $converter = new CommonMarkConverter($config, $environment);
    $md = file_get_contents($f);
    $mde = LitEmoji::encodeUnicode($md);
    $html = $converter->convertToHtml($mde);
    echo $html;
}

function jail_check($f): bool
{
    $baseDir = realpath(DOCUMENTS);
    $baseDirLength = strlen($baseDir);
    $filepath = realpath(DOCUMENTS . $f);
    if (substr($filepath, 0, $baseDirLength) == $baseDir) {
        return true;
    } else {
        return false;
    }
}
