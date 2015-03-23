<!DOCTYPE html>
<html>
<head>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">


    <link rel="stylesheet" type="text/css" href="mystyle.css">

    <link href="//fonts.googleapis.com/css?family=Lato:100normal,100italic,300normal,300italic,400normal,400italic,700normal,700italic,900normal,900italic|Open+Sans:400normal|Roboto:400normal|Oswald:400normal|Open+Sans:400normal|Source+Sans+Pro:400normal|Indie+Flower:400normal|Gloria+Hallelujah:400normal|PT+Sans:400normal|Raleway:400normal|Droid+Sans:400normal&amp;subset=all" rel="stylesheet" type="text/css">
</head>
<body>
<?php

/**
 * Created by PhpStorm.
 * User: paras
 * Date: 3/5/15
 * Time: 3:45 PM
 */

class LogEntry {
    public $revision;
    public $date;
    public $msg;
    public $author;
    public $paths;
}

class Path {
    public $kind;
    public $action;
    public $uri;
}

class File {
    public $kind;
    public $path;
    public $size = 0;
    public $most_recent_commit;
    public $commits = array(); // a list of all the commits that affect the file stored in format list<LogEntry>
}

/*<commit
   revision="7466">
<author>sud2</author>
<date>2014-10-17T17:47:49.873309Z</date>
</commit>
*/
class Commit {
    public $revision;
    public $author;
    public $date;
}

class Project {
    public $title;
    public $date;
    public $version;
    public $summary;
    public $files = array();
}

class ChangeEntry {
    public $revision;
    public $date;
    public $msg;
    public $author;
    public $paths;
}

class Change {
    public $revision;
    public $date;
    public $msg;
    public $author;
    public $kind;
    public $file;
    public $action;

    public function __toString() {
        return "Change (rev $this->revision) affecting $this->file with message $this->msg";
    }
}

$listData = simplexml_load_file("/home/paras/Desktop/logdata/svn_list.xml")->list;
$files = array();
foreach($listData->entry as $entry) {
    $file = new File();
    $file->kind = $entry->attributes()['kind'];
    $file->path = (string)$entry->name;
    if ($entry->size) {
        $file->size = $entry->size;
    }
    $commit = new Commit();
    $commit->revision = $entry->revision;
    $commit->author = $entry->author;
    $commit->date = substr($entry->date, 0, 10);
    $file->most_recent_commit = $commit;
    $files[] = $file;
}

function contains($needle, $haystack) {
    return strpos($haystack, $needle) !== false;
}

$file_map = array();
foreach($files as $file) {
    $path = $file->path;
    $file_map[$path] = $file;
}

$xmlData = simplexml_load_file("/home/paras/Desktop/logdata/svn_log.xml");

$changes = array();

foreach($xmlData->logentry as $log) {
    foreach ($log->paths->children() as $pathvar) {
        $change = new Change();
        $change->revision = $log->attributes()['revision'];
        $change->date = substr($log->date, 0, 10);
        $change->msg = (string)$log->msg;
        $change->author = $log->author;
        $change->kind=$pathvar->attributes()['kind'];
        $change->action=$pathvar->attributes()['action'];
        $change->file=substr((string)$pathvar, 6);

        $changes[] = $change;
    }
}

function compareUsingRevision($aObj, $bObj) {
    $a = (int)$aObj->revision;
    $b = (int)$bObj->revision;
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

usort($changes, compareUsingRevision);

/*
 * Each version of each file in the project
The number is the revision number for that commit
The author is the netid of the committer
The info is the commit message for that revision
The date is the date of that commit
 */

foreach ($logentries as $entry) {
    foreach ($entry->paths as $path) {
        $file_affected = $file_map[$path->uri];
        if ($path->uri == "shopping/list1.txt") {
            echo "<br>BBB";
            echo $path->uri;
            echo "<br>";
        }
        echo "<br>$entry->revision</br>";
        $file_affected->commits[] = $entry;
        if ($entry->revision == 548) {

            echo "<br>";
            echo gettype($path->uri);
            echo $path->uri;
            echo "<br>";
            echo gettype($file_map["shopping/list1.txt"]);
            echo "<br>";
//            echo $path->uri;
//            echo gettype($file_map[$path->uri]);
        }
    }

    if ($entry->revision == 502) {
        echo "YES";
        echo $entry->author;
        echo count($entry->paths);
        foreach($entry->paths as $path) {
            echo $path->uri;
            echo count($file_map[$path->uri]->commits);
        }
    }
}

// Assumes the filename has an extension
// Returns the extension
function getExtension($filename) {
    $index = strrpos($filename, ".");
    return substr($filename, $index + 1);
}

// Assumes the filename has a '/'
// Returns the filename from the filepath
function getFileName($path) {
    $index = strrpos($path, "/");
    return substr($path, $index + 1);
}

$file_param = $_GET["file"];
?>

<div id = "top">
<?php
if( $file_param && $file_map[$file_param]) {
    $file_name = getFileName($file_param);
    $file = $file_map[$file_param];
    echo "<h1> $file->path ($file->kind) </h1> <br>";
    ?>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#iframe">
        Open in iframe
    </button>
    <div id="iframe" class = "collapse">
        <iframe src="/home/paras/Desktop/logdata/svn_log.xml"></iframe>
    </div>
    <?php
    if ($file->size != 0) {
        echo "<h3> $file->size bytes </h3>";
    }

    // if filename has a '.'
    if (strrpos($file_name, ".") !== FALSE) {
        $ext = getExtension($file_name);
        if ($ext == "java" || $ext == "py") {
            $type = "code";
        } elseif ($ext == "ico" || $ext == "png") {
            $type = "image";
        } elseif ($ext == "class") {
            $type = "Java class file";
        } elseif ($ext == "docx") {
            $type = "documentation";
        } elseif ($ext == "project" || $ext == "classpath" || $ext == "settings" || $ext == "prefs" || $ext == "pydevproject") {
            $type = "IDE config file";
        } else {
            $type = "resource";
        }
        echo "<h3> File type: $type </h3>";
    }
    echo "<h3>Revisions:</h3><hr>";
    echo "<div id='versions'>";
    foreach ($changes as $change) {
        if (strpos($file_param, $change->file) === 0) {
            echo "<ul class='list-group'>";
            echo "<li class='list-group-item revision'><span>";
            echo "Revision: $change->revision <br>";
            echo "</li></span>";
            echo "<li class='list-group-item author'><span>";
            echo "Author: $change->author <br>";
            echo "</li></span>";
            echo "<li class='list-group-item info'><span>";
            if ($change->msg) {
                echo "Info: $change->msg <br>";
            } else {
                echo "Info: [empty] <br>";
            }
            echo "</li></span>";
            echo "<li class='list-group-item date'><span>";
            echo "Date: $change->date <br>";
            echo "</li></span>";
            echo "<li class='list-group-item file-affected'><span>";
            echo "File Affected: $change->file <br>";
            echo "</li></span>";
            echo "<li class='list-group-item action'><span>";
            if ($change->action == "M") {
                echo "Action: Modified <br>";
            } elseif ($change->action == "A") {
                echo "Action: Added <br>";
            } elseif ($change->action == "D") {
                echo "Action: Deleted <br>";
            } else {
                echo "Info: [empty] <br>";
            }
            echo "</li></span>";
            echo "</ul>";
        }
    }
    echo "</div>";
} else {
    echo "<h1>Invalid page</h1>";
}

?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

</body>
</html>