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
    $commit->revision = (string)$entry->commit->attributes()['revision'];
    $commit->author = $entry->commit->author;
    $commit->date = substr($entry->commit->date, 0, 10);
    $file->most_recent_commit = $commit;
    $files[] = $file;
}

function contains($needle, $haystack) {
    return strpos($haystack, $needle) !== false;
}

$xmlData = simplexml_load_file("/home/paras/Desktop/logdata/svn_log.xml");
$logentries = array();
foreach($xmlData->logentry as $log) {
    $entry = new LogEntry();
    $entry->revision = (string)$log->attributes()['revision'];
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
    $logentries[$entry->revision] = $entry;
}

$projects = array();
foreach($files as $file) {
    $path = $file->path;
    if (!contains('/', $path) && !array_key_exists($path, $project)) {
        // is project
        $project = new Project();
        $project->title = $path;
        $project->date = $logentries[$file->most_recent_commit->revision]->date;
        $project->version = $logentries[$file->most_recent_commit->revision]->revision;
        $project->summary = $logentries[$file->most_recent_commit->revision]->msg;
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

$i = 0;
foreach ($projects as $project) {
$i++;
?>
<div class = "row project">
    <div class="col-md-8">
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="<?php echo "#demo$i" ?>">
            <?php
            echo $project-> title;
            ?>
        </button>
    </div>
    <div class = "col-md-4 project_info">
        Date: <?php echo "$project->date" ?>
        <br>
        Version: <?php echo "$project->version" ?>
        <br>
        Summary: <?php echo "$project->summary" ?>
        <br>
    </div>
</div>
<div id ="<?php echo "demo$i" ?>" class="collapse">
    <?php
        foreach ($project->files as $file) {
            ?>
            <div class="demo-item">
                <div>
                    <?php
                        $url = "Follow.php?file=$file->path";
                    ?>
                    <a href = "<?php echo $url ?>" >
                        <pre><?php echo $file->path ?></pre>
                    </a>
                </div>
            </div>
            <?php
        }
        echo "</div><br><hr>";
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
            $file_affected->commits[] = $entry;
        }
    }
    ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</body>
