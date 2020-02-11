<?
function getFilePathList($path, &$result = []) {
    if (!file_exists($path)) {
        return $result;
    }

    $arDir = [];
    foreach (scandir($path) as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        if (is_dir($path.$file)) {
            $arDir[] = $path . $file . '/';
        } else {
            $result[] = $path . $file;
        }
    }
    foreach ($arDir as $dir) {
        getFilePathList($dir, $result);
    }

    return $result;
}

foreach (getFilePathList(__DIR__ . '/classes/') as $file) {
    if(file_exists($file)) {
        require_once($file);
    }
}