<!DOCTYPE html>
<html>
<head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

    <link rel="stylesheet" type="text/css" href="mystyle.css">
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

class FileS {
    public $name;
}

class DirS {
    public $name;
    public $children = array();
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

$projects = array();
foreach($files as $file) {
    $path = $file->path;
    if (!contains('/', $path) && !array_key_exists($path, $project)) {
        // is project
        $project = new Project();
        $project->title = $path;
        $project->summary = "bob";
        $projects[$path] = $project;
    }
}

foreach($files as $file) {
    $path = $file->path;
    if (!contains('/', $path)) {
        // is project
    } else {
        $index = strpos($path, '/');
        $project_name = substr($path, 0, $index);
        $projects[$project_name]->files[] = $file;
    }
}

$file_map = array();
foreach($files as $file) {
    $path = $file->path;
    $file_map[$path] = $file;
}

$xmlData = simplexml_load_file("/home/paras/Desktop/logdata/svn_log.xml");
$logentries = array();
foreach($xmlData->logentry as $log) {
    $entry = new LogEntry();
    $entry->revision = $log->attributes()['revision'];
    $entry->date = substr($log->date, 0, 10);
    $entry->msg = $log->msg;
    $entry->author = $log->author;
    $entry->paths=array();
    foreach ($log->paths->children() as $pathvar) {
        $path = new Path();
        $path->kind=$pathvar->attributes()['kind'];
        $path->action=$pathvar->attributes()['action'];
        $path->uri=substr((string)$pathvar, 6);
        $entry->paths[] = $path;
    }
    $logentries[] = $entry;
}
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
echo "<br>AAA";
echo count($file_map["shopping/list1.txt"]->commits);
echo "<br>";

$file_param = $_GET["file"];
if( $file_param && $file_map[$file_param]) {
    $file_name = $file_param;
    $file = $file_map[$file_name];
    echo "<h1> $file->path ($file->kind) </h1> <br>";
    if ($file->size != 0) {
        echo "<h3> $file->size bytes </h3>";
    }
    echo "<h3>Revisions:</h3><hr>";
    echo count($file->commits);
    echo "<div id='versions'>";
    foreach ($file->commits as $entry) {
        echo "<ul class='list-group'>";
        echo "<li class='list-group-item'><span class='revision'>";
        echo "Revision: $entry->revision <br>";
        echo "</li></span>";
        echo "<li class='list-group-item'><span class='author'>";
        echo "Author: $entry->author <br>";
        echo "</li></span>";
        echo "<li class='list-group-item'><span class='info'>";
        echo "Info: $entry->msg <br>";
        echo "</li></span>";
        echo "<li class='list-group-item'><span class='date'>";
        echo "Date: $entry->date <br>";
        echo "</li></span>";
        echo "</ul>";
    }
    echo "</div>";
} else {
    echo "<h1>Invalid page</h1>";
}

?>