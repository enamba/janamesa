<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 09.01.2012
 */

$projects = array(
    "yd",
    "yd-backend",
    "yd-partner",
);

$info = false;
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $info = "Failed: cannot updload .po file";
    if (isset($_FILES['po']['tmp_name']) && is_uploaded_file($_FILES['po']['tmp_name'])) {
        if (substr($_FILES['po']['name'], -3) != ".po") {
            $info = "Failed: not a .po file";
        }
        else {
            $po = file_get_contents($_FILES['po']['tmp_name']);

            $project = false;
            if (preg_match("/Project-Id-Version: ([a-z\-]+)/", $po, $matches)) {
                $project = $matches[1];
            }

            $revision = false;
            if (preg_match("/POT-Creation-Date: ([0-9]{4}-[0-9]{2}-[0-9]{2})/", $po, $matches)) {
                $revision = $matches[1];
            }

            $language = false;
            if (preg_match("/Language: ([a-z]{2}_[A-Z]{2})/", $po, $matches)) {
                $language = $matches[1];
            }

            if (!in_array($project, $projects)) {
                $info = "Failed: unknow project " . $project;
            }
//            elseif ($revision != date("Y-m-d")) {
//                $info = "Failed: your .po file is outdated (" . $revision . ")";
//            }
            elseif ($language === false || !is_dir("./" . $language . "/LC_MESSAGES")) {
                $info = "Failed: unknow language " . $language;
            }
            elseif (move_uploaded_file($_FILES['po']['tmp_name'], "./" . $language . "/LC_MESSAGES/" . $project . ".po")) {
                $info = ".po file was successfully uploaded to ./" . $language . "/LC_MESSAGES/" . $project . ".po";
            }
            else {
                $info = "Failed: cannot move " . $_FILES['po']['tmp_name'];
            }
        }
    }
}

$project = "yd";
if (isset($_GET['p']) && in_array($_GET['p'], $projects)) {
    $project = $_GET['p'];
}
$pot = $project . ".pot";
$po = $project . ".po";

$locales = array();
if ($handle = opendir("./")) {
    while (($dir = readdir($handle)) !== false) {
        if (!is_dir($dir) || $dir == "." || $dir == "..") {
            continue;
        }

        if (is_file("./" . $dir . "/LC_MESSAGES/" . $po)) {
            $stats = exec("msgfmt --statistics " . escapeshellarg("./" . $dir . "/LC_MESSAGES/" . $po) . " --output=/dev/null 2>&1");

            $locales[$dir] = array(
                'title' => $stats,
                'translated' => 0,
                'fuzzy' => 0,
                'untranslated' => 0);

            $stats = explode(",", $stats);
            foreach ($stats as $stat) {
                if (strpos($stat, "untranslated")) {
                    $locales[$dir]['untranslated'] = (integer) $stat;
                }
                elseif (strpos($stat, "translated")) {
                    $locales[$dir]['translated'] = (integer) $stat;
                }
                elseif (strpos($stat, "fuzzy")) {
                    $locales[$dir]['fuzzy'] = (integer) $stat;
                }
            }
        }
    }
    closedir($handle);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

    <head>
        <title>yd. Translation Statistics</title>

        <style type="text/css">
            *{
                font-family: inherit;
                font-size: inherit;
                margin: 0px;
                padding: 0px;
            }
            table{
                border-collapse: collapse;
            }
            table td {
                padding: 10px;
            }
            p, form{
                margin: 10px;
            }
            div.percent{
                height: 20px;
                width: 500px;
            }
            div.percent div{
                height: 20px;
                float: left;
            }
            div.untranslated{
                background: red;
            }
            div.translated{
                background: green;
            }
            div.fuzzy{
                background: yellow;
            }
            p.alert{
                color: red;
                font-weight: bold;
                text-decoration: blink;
            }
        </style>
    </head>

    <body>
        <?php if (!file_exists("/usr/bin/msgfmt")) : ?>
        <p>Please install gettext package</p>
        <?php endif; ?>

        <?php if (date("Ymd", filemtime("./" . $pot)) != date("Ymd")) : ?>
        <p class="alert">
            The translation is not up-to-date, please invoke <a href="mailto:springer@lieferando.de">The Springer</a>
        </p>
        <?php endif; ?>

        <p>
            <a href="/poedit.pdf">Before you start please read this first</a>
        </p>

        <p>
            Projects:
            <?php if ($project != "yd") : ?><a href="/?p=yd"><?php endif; ?>Frontend<?php if ($project != "yd") : ?></a><?php endif; ?> |
            <?php if ($project != "yd-backend") : ?><a href="/?p=yd-backend"><?php endif; ?>Backend<?php if ($project != "yd-backend") : ?></a><?php endif; ?> | 
            <?php if ($project != "yd-partner") : ?><a href="/?p=yd-partner"><?php endif; ?>Partner<?php if ($project != "yd-partner") : ?></a><?php endif; ?>
        </p>

        <?php if ($info !== false) : ?>
        <p><?php echo $info; ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="file" name="po" />
            <input type="submit" value=".po Upload" />
        </form>

        <p>
            <a href="/<?php echo $pot; ?>"><?php echo $pot; ?></a> generated on <?php echo date("Y-m-d H:i:s", filemtime("./" . $pot)); ?>
        </p>

        <table>
            <?php foreach ($locales as $locale => $stats) : ?>
            <tr>
                <td>
                    <a href="/<?php echo $locale; ?>/LC_MESSAGES/<?php echo $po; ?>"><b><?php echo $locale; ?></b></a>
                </td>
                <td>
                    <div class="percent" title="<?php echo $stats['title']; ?>">
                        <?php unset($stats['title']); ?>

                        <?php foreach ($stats as $class => $stat) : ?>
                        <div class="<?php echo $class; ?>" style="width:<?php echo $stat / array_sum($stats) * 100; ?>%;"></div>
                        <?php endforeach; ?>
                    </div>
                </td>
                <td>
                    <a href="/<?php echo $locale; ?>/LC_MESSAGES/<?php echo $po; ?>">Download</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </body>

</html>