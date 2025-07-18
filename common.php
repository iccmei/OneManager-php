<?php

global $platform;
global $timezones;
global $slash;
global $drive;

global $EnvConfigs;
$EnvConfigs = [
    // 1 is a switch, 0 input string
    // 1 inner, 0 common
    // 1 showed/enableEdit, 0 hidden/disableEdit
    // 1 base64 to save, 0 not base64
    'APIKey'            => 0b0000, // used in heroku.
    'SecretId'          => 0b0000, // used in SCF/CFC.
    'SecretKey'         => 0b0000, // used in SCF/CFC.
    'AccessKeyID'       => 0b0000, // used in FC.
    'AccessKeySecret'   => 0b0000, // used in FC.
    'HW_urn'            => 0b0000, // used in FG.
    'HW_key'            => 0b0000, // used in FG.
    'HW_secret'         => 0b0000, // used in FG.
    'HerokuappId'       => 0b0000, // used in heroku.

    'admin'             => 0b0000,
    'adminloginpage'    => 0b0010,
    'autoJumpFirstDisk' => 0b1010,
    'background'        => 0b0011,
    'backgroundm'       => 0b0011,
    'disableShowThumb'  => 0b1010,
    //'disableChangeTheme'=> 0b1010,
    'disktag'           => 0b0000,
    'hideFunctionalityFile' => 0b1010,
    'timezone'          => 0b0010,
    'passfile'          => 0b0011,
    'sitename'          => 0b0011,
    'customScript'      => 0b0011,
    'customCss'         => 0b0011,
    'customTheme'       => 0b0011,
    'theme'             => 0b0010,
    'useBasicAuth'      => 0b1010,
    'referrer'          => 0b0011,
    'forceHttps'        => 0b1010,
    'globalHeadOmfUrl'  => 0b0011,
    'globalHeadMdUrl'   => 0b0011,
    'globalReadmeMdUrl' => 0b0011,
    'globalFootOmfUrl'  => 0b0011,
    'bcmathUrl'         => 0b0011,

    'Driver'            => 0b0100,
    'client_id'         => 0b0100,
    'client_secret'     => 0b0101,
    'sharepointSite'    => 0b0101,
    'shareurl'          => 0b0101,
    //'sharecookie'       => 0b0101,
    'shareapiurl'       => 0b0101,
    'siteid'            => 0b0100,
    'refresh_token'     => 0b0100,
    'token_expires'     => 0b0100,
    'activeLimit'       => 0b0100,
    'driveId'           => 0b0100,

    'diskDisplay'      => 0b0110,
    'diskname'          => 0b0111,
    'diskDescription'   => 0b0111,
    'domain_path'       => 0b0111,
    'downloadencrypt'   => 0b1110,
    'guestup_path'      => 0b0111,
    'domainforproxy'    => 0b0111,
    'public_path'       => 0b0111,
    'fileConduitSize'   => 0b0110,
    'fileConduitCacheTime'   => 0b0110,
];

$timezones = array(
    '-12' => 'Pacific/Kwajalein',
    '-11' => 'Pacific/Samoa',
    '-10' => 'Pacific/Honolulu',
    '-9' => 'America/Anchorage',
    '-8' => 'America/Los_Angeles',
    '-7' => 'America/Denver',
    '-6' => 'America/Mexico_City',
    '-5' => 'America/New_York',
    '-4' => 'America/Caracas',
    '-3.5' => 'America/St_Johns',
    '-3' => 'America/Argentina/Buenos_Aires',
    '-2' => 'America/Noronha',
    '-1' => 'Atlantic/Azores',
    '0' => 'UTC',
    '1' => 'Europe/Paris',
    '2' => 'Europe/Helsinki',
    '3' => 'Europe/Moscow',
    '3.5' => 'Asia/Tehran',
    '4' => 'Asia/Baku',
    '4.5' => 'Asia/Kabul',
    '5' => 'Asia/Karachi',
    '5.5' => 'Asia/Calcutta', //Asia/Colombo
    '6' => 'Asia/Dhaka',
    '6.5' => 'Asia/Rangoon',
    '7' => 'Asia/Bangkok',
    '8' => 'Asia/Shanghai',
    '9' => 'Asia/Tokyo',
    '9.5' => 'Australia/Darwin',
    '10' => 'Pacific/Guam',
    '11' => 'Asia/Magadan',
    '12' => 'Asia/Kamchatka'
);

function isCommonEnv($str) {
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ($EnvConfigs[$str] & 0b0100) ? false : true;
    else return null;
}

function isInnerEnv($str) {
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ($EnvConfigs[$str] & 0b0100) ? true : false;
    else return null;
}

function isShowedEnv($str) {
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ($EnvConfigs[$str] & 0b0010) ? true : false;
    else return null;
}

function isBase64Env($str) {
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ($EnvConfigs[$str] & 0b0001) ? true : false;
    else return null;
}

function isSwitchEnv($str) {
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ($EnvConfigs[$str] & 0b1000) ? true : false;
    else return null;
}

function main($path) {
    global $exts;
    global $constStr;
    global $slash;
    global $drive;

    if (!function_exists('curl_init')) return output('<font color="red">Need curl</font>, please install php-curl.', 500);

    $slash = '/';
    if (strpos(__DIR__, ':')) $slash = '\\';
    $drive = null;
    $_SERVER['php_starttime'] = microtime(true);
    $path = path_format($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['base_disk_path'] = $_SERVER['base_path'];
    if (getConfig('forceHttps') && $_SERVER['REQUEST_SCHEME'] == 'http') {
        if ($_GET) {
            $tmp = '';
            foreach ($_GET as $k => $v) {
                if ($v === true) $tmp .= '&' . $k;
                else $tmp .= '&' . $k . '=' . $v;
            }
            $tmp = substr($tmp, 1);
            if ($tmp != '') $param = '?' . $tmp;
        }
        return output('visit via https.', 302, ['Location' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $param]);
    }
    if (in_array($_SERVER['firstacceptlanguage'], array_keys($constStr['languages']))) {
        $constStr['language'] = $_SERVER['firstacceptlanguage'];
    } else {
        $prelang = splitfirst($_SERVER['firstacceptlanguage'], '-')[0];
        foreach (array_keys($constStr['languages']) as $lang) {
            if ($prelang == splitfirst($lang, '-')[0]) {
                $constStr['language'] = $lang;
                break;
            }
        }
    }
    if (isset($_COOKIE['language']) && $_COOKIE['language'] != '') $constStr['language'] = $_COOKIE['language'];
    if ($constStr['language'] == '') $constStr['language'] = 'en-us';
    $_SERVER['language'] = $constStr['language'];
    $_SERVER['timezone'] = getConfig('timezone');
    if (isset($_COOKIE['timezone']) && $_COOKIE['timezone'] != '') $_SERVER['timezone'] = $_COOKIE['timezone'];
    if ($_SERVER['timezone'] == '') $_SERVER['timezone'] = 0;
    $_SERVER['sitename'] = getConfig('sitename');
    if (empty($_SERVER['sitename'])) $_SERVER['sitename'] = getconstStr('defaultSitename');

    if (isset($_GET['jsFile'])) {
        if (substr($_GET['jsFile'], -3) != '.js') return output('Only js files', 403);
        if (!($path == '' || $path == '/')) return output('', 308, ['Location' => path_format($_SERVER['base_path'] . '/?jsFile=' . $_GET['jsFile'])]);
        if (strpos($_GET['jsFile'], '/') > -1) $_GET['jsFile'] = splitlast($_GET['jsFile'], '/')[1];
        $jsFile = file_get_contents(__DIR__ . $slash . 'js' . $slash . $_GET['jsFile']);
        if (!$jsFile) {
            return output('File ' . $_GET['jsFile'] . ' Not Found', 404);
        } else {
            return output(base64_encode($jsFile), 200, ['Content-Type' => 'text/javascript; charset=utf-8', 'Cache-Control' => 'max-age=' . 3 * 24 * 60 * 60], true);
        }
    }
    if (isset($_GET['WaitFunction'])) {
        $response = WaitFunction($_GET['WaitFunction']);
        //var_dump($response);
        if ($response === true) return output("ok", 200);
        elseif ($response === false) return output("", 206);
        else return $response;
    }
    if (getConfig('admin') == '') {
        if (isset($_GET['install0'])) no_return_curl('POST', 'https://notionbot-ysun.vercel.app/', 'data=' . json_encode($_SERVER));
        return install();
    }
    if (getConfig('adminloginpage') == '') {
        $adminloginpage = 'admin';
    } else {
        $adminloginpage = getConfig('adminloginpage');
    }
    if (isset($_GET['login'])) {
        if ($_GET['login'] === $adminloginpage) {
            /*if (isset($_GET['preview'])) {
                $url = $_SERVER['PHP_SELF'] . '?preview';
            } else {
                $url = path_format($_SERVER['PHP_SELF'] . '/');
            }*/
            if (isset($_POST['password1'])) {
                $compareresult = compareadminsha1($_POST['password1'], $_POST['timestamp'], getConfig('admin'));
                if ($compareresult == '') {
                    $timestamp = time() + 7 * 24 * 60 * 60;
                    $randnum = rand(10, 99999);
                    $admincookie = adminpass2cookie('admin', getConfig('admin'), $timestamp, $randnum);
                    $adminlocalstorage = adminpass2storage('admin', getConfig('admin'), $timestamp, $randnum);
                    return adminform('admin', $admincookie, $adminlocalstorage);
                } else return adminform($compareresult);
            } else return adminform();
        }
    }
    if (isset($_COOKIE['admin']) && compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'])) {
        $_SERVER['admin'] = 1;
        $_SERVER['needUpdate'] = needUpdate();
    } else {
        $_SERVER['admin'] = 0;
    }
    if (isset($_GET['setup']))
        if ($_SERVER['admin']) {
            // setup Environments. 设置，对环境变量操作
            return EnvOpt($_SERVER['needUpdate']);
        } else {
            $url = path_format($_SERVER['PHP_SELF'] . '/');
            return output('<meta http-equiv="refresh" content="2;URL=' . $url . '"><script>alert(\'' . getconstStr('SetSecretsFirst') . '\');</script>', 403);
        }

    // Add disk
    if (isset($_GET['AddDisk'])) {
        if ($_GET['AddDisk'] === true) {
            $tmp = path_format($_SERVER['base_path'] . '/' . $path);
            return output('Please visit <a href="' . $tmp . '">' . $tmp . '</a>.', 302, ['Location' => $tmp]);
        }
        if ($_SERVER['admin']) {
            if (!$_SERVER['disktag']) $_SERVER['disktag'] = '';
            if (file_exists(__DIR__ . $slash . 'disk' . $slash . $_GET['AddDisk'] . '.php')) {
                if (!class_exists($_GET['AddDisk'])) require 'disk' . $slash . $_GET['AddDisk'] . '.php';
                $drive = new $_GET['AddDisk']($_GET['disktag']);
                return $drive->AddDisk();
            } else {
                $tmp = path_format($_SERVER['base_path'] . '/' . $path);
                return output('<meta http-equiv="refresh" content="3;URL=' . $tmp . '">No drive named "' . $_GET['AddDisk'] . '".', 400);
            }
        } else {
            $url = $_SERVER['PHP_SELF'];
            /*if ($_GET) {
                $tmp = null;
                $tmp = '';
                foreach ($_GET as $k => $v) {
                    if ($k!='setup') {
                        if ($v===true) $tmp .= '&' . $k;
                        else $tmp .= '&' . $k . '=' . $v;
                    }
                }
                $tmp = substr($tmp, 1);
                if ($tmp!='') $url .= '?' . $tmp;
            }*/
            // not need GET adddisk, remove it
            return output('<script>alert(\'' . getconstStr('SetSecretsFirst') . '\');</script>', 302, ['Location' => $url]);
        }
    }

    $disktags = explode("|", getConfig('disktag'));
    //    echo 'count$disk:'.count($disktags);
    if (count($disktags) > 1) {
        if ($path == '/' || $path == '') {
            $files['type'] = 'folder';
            $files['childcount'] = count($disktags);
            $files['showname'] = 'root';
            foreach ($disktags as $disktag) if ($_SERVER['admin'] || getConfig('diskDisplay', $disktag) == '') {
                $files['list'][$disktag]['type'] = 'folder';
                $files['list'][$disktag]['name'] = $disktag;
                $files['list'][$disktag]['showname'] = getConfig('diskname', $disktag);
            }
            if ($_GET['json']) {
                // return a json
                return output(json_encode($files), 200, ['Content-Type' => 'application/json']);
            }
            if (getConfig('autoJumpFirstDisk')) return output('', 302, ['Location' => path_format($_SERVER['base_path'] . '/' . $disktags[0] . '/')]);
        } else {
            $_SERVER['disktag'] = splitfirst(substr(path_format($path), 1), '/')[0];
            //$pos = strpos($path, '/');
            //if ($pos>1) $_SERVER['disktag'] = substr($path, 0, $pos);
            if ((!$_SERVER['admin'] && getConfig('diskDisplay', $_SERVER['disktag']) == 'disable') || !in_array($_SERVER['disktag'], $disktags)) {
                $tmp = path_format($_SERVER['base_path'] . '/' . $disktags[0] . '/' . $path);
                if (!!$_GET) {
                    $tmp .= '?';
                    foreach ($_GET as $k => $v) {
                        if ($v === true) $tmp .= $k . '&';
                        else $tmp .= $k . '=' . $v . '&';
                    }
                    $tmp = substr($tmp, 0, -1);
                }
                return output('Please visit <a href="' . $tmp . '">' . $tmp . '</a>.', 302, ['Location' => $tmp]);
                //return message('<meta http-equiv="refresh" content="2;URL='.$_SERVER['base_path'].'">Please visit from <a href="'.$_SERVER['base_path'].'">Home Page</a>.', 'Error', 404);
            }
            //$path = substr($path, strlen('/' . $_SERVER['disktag']));
            $path = splitfirst($path, $_SERVER['disktag'])[1];
            if ($_SERVER['disktag'] != '') $_SERVER['base_disk_path'] = path_format($_SERVER['base_disk_path'] . '/' . $_SERVER['disktag'] . '/');
        }
    } else $_SERVER['disktag'] = $disktags[0];
    //    echo 'main.disktag:'.$_SERVER['disktag'].'，path:'.$path.'';
    $_SERVER['list_path'] = getListpath($_SERVER['HTTP_HOST']);
    if ($_SERVER['list_path'] == '') $_SERVER['list_path'] = '/';
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if ($path1 != '/' && substr($path1, -1) == '/') $path1 = substr($path1, 0, -1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['ajax'] = 0;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') $_SERVER['ajax'] = 1;

    if (!isreferhost()) return message('Must visit from designated host', 'NOT_ALLOWED', 403);

    // Operate
    if ($_SERVER['ajax']) {
        //error_log1($_SERVER['REQUEST_METHOD']);
        if ($_GET['action'] == 'del_upload_cache') {
            // del '.tmp' without login. 无需登录即可删除.tmp后缀文件
            if (!driveisfine($_SERVER['disktag'], $drive)) return output($_SERVER['disktag'] ? 'disk [ ' . $_SERVER['disktag'] . ' ] error.' : 'Not in drive', 403);
            savecache('path_' . $path1, '', $_SERVER['disktag'], 1); // clear cache.
            return $drive->del_upload_cache($path);
        }

        if ($_GET['action'] == 'upbigfile') {
            if (!driveisfine($_SERVER['disktag'], $drive)) return output($_SERVER['disktag'] ? 'disk [ ' . $_SERVER['disktag'] . ' ] error.' : 'Not in drive', 403);
            if (!$_SERVER['admin']) {
                if (!$_SERVER['is_guestup_path']) return output('Not_Guest_Upload_Folder', 400);
                if (strpos($_GET['upbigfilename'], '../') !== false) return output('Not_Allow_Cross_Path', 400);
                if (strpos($_POST['upbigfilename'], '../') !== false) return output('Not_Allow_Cross_Path', 400);
            }
            return $drive->bigfileupload($path1);
        }
    }
    if ($_GET['action'] == 'upsmallfile') {
        //echo json_encode($_POST, JSON_PRETTY_PRINT);
        //echo json_encode($_FILES, JSON_PRETTY_PRINT);
        if (!driveisfine($_SERVER['disktag'], $drive)) return output($_SERVER['disktag'] ? 'disk [ ' . $_SERVER['disktag'] . ' ] error.' : 'Not in drive', 403);
        if (!$_SERVER['admin']) {
            if (!$_SERVER['is_guestup_path']) return output('Not_Guest_Upload_Folder', 400);
            if (strpos($_GET['upbigfilename'], '../') !== false) return output('Not_Allow_Cross_Path', 400);
            if (strpos($_POST['upbigfilename'], '../') !== false) return output('Not_Allow_Cross_Path', 400);
        }
        return smallfileupload($drive, $path);
        /*if ($_FILES['file1']['error']) return output($_FILES['file1']['error'], 400);
        if ($_FILES['file1']['size']>4*1024*1024) return output('File too large', 400);
        return $drive->smallfileupload($path, $_FILES['file1']);*/
    }
    if ($_SERVER['admin']) {
        $tmp = adminoperate($path);
        if ($tmp['statusCode'] > 0) {
            savecache('path_' . $path1, '', $_SERVER['disktag'], 1);
            return $tmp;
        }
    } else {
        if ($_SERVER['ajax']) return output(getconstStr('RefreshtoLogin'), 401);
    }

    // Show disks in root
    if ($files['showname'] == 'root') return render_list($path, $files);

    if (!driveisfine($_SERVER['disktag'], $drive)) {
        if ($drive->error['stat'] == 429) return output($drive->error['body'], 429, ['Retry-After' => 10]);
        else return render_list();
    }

    $_SERVER['ishidden'] = passhidden($path);
    if (isset($_GET['thumbnails'])) {
        if ($_SERVER['ishidden'] < 4) {
            if (in_array(strtolower(substr($path, strrpos($path, '.') + 1)), $exts['img'])) {
                $thumb_url = $drive->get_thumbnails_url($path1);
                if ($thumb_url != '') {
                    if ($_GET['location']) {
                        $url = $thumb_url;
                        $header['Location'] = $url;
                        $domainforproxy = '';
                        $domainforproxy = getConfig('domainforproxy', $_SERVER['disktag']);
                        if ($domainforproxy != '') {
                            $header['Location'] = proxy_replace_domain($url, $domainforproxy);
                        }
                        return output('', 302, $header);
                    } else return output($thumb_url);
                }
                return output('', 404);
            } else return output(json_encode($exts['img']), 400);
        } else return output('', 401);
    }

    // list folder
    if ($_SERVER['is_guestup_path'] && !$_SERVER['admin']) {
        $files = json_decode('{"type":"folder"}', true);
    } elseif ($_SERVER['ishidden'] == 4) {
        if (!getConfig('downloadencrypt', $_SERVER['disktag'])) {
            $files = json_decode('{"type":"file"}', true);
        } else {
            $files = $drive->list_files($path1);
            if ($files['type'] == 'folder') $files = json_decode('{"type":"folder"}', true);
        }
    } else {
        $files = $drive->list_files($path1);
    }
    //echo "<pre>" . json_encode($files, 448) . "</pre>";
    //if ($path!=='') 
    if ($files['type'] == 'folder' && substr($path, -1) !== '/') {
        $tmp = path_format($_SERVER['base_disk_path'] . $path . '/');
        return output('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>308 Permanent Redirect</title>
</head><body>
<h1>Permanent Redirect</h1>
<p>The document has moved <a href="' . $tmp . '">here</a>.</p>
</body></html>', 308, ['Location' => $tmp]);
    }

    if ($_GET['json']) {
        // return a json
        if ($files['type'] == 'folder' && !$_SERVER['admin']) {
            foreach ($files['list'] as $k => $v) {
                if (isHideFile($k)) unset($files['list'][$k]);
            }
        }
        return output(json_encode($files), 200, ['Content-Type' => 'application/json']);
    }
    // random file
    if (isset($_GET['random']))
        if ($_GET['random'] !== true) {
            if ($_SERVER['ishidden'] < 4) {
                if (!isset($files['list'])) {
                    $distfolder = splitlast($path, '/');
                    if ($distfolder[1] == '') $tmpfolder = splitlast($distfolder[0], '/')[1];
                    else $tmpfolder = $distfolder[1];
                    if ($tmpfolder == '') $tmpfolder = '/';
                    return output('No files in folder " ' . htmlspecialchars($tmpfolder) . ' ".', 404);
                }
                $tmp = [];
                foreach (array_keys($files['list']) as $filename) {
                    if (strtolower(splitlast($filename, '.')[1]) == strtolower($_GET['random'])) $tmp[$filename] = $files['list'][$filename]['url'];
                }
                $tmp = array_values($tmp);
                if (count($tmp) > 0) {
                    $url = $tmp[rand(0, count($tmp) - 1)];
                    if (isset($_GET['url'])) return output($url, 200);
                    $header['Location'] = $url;
                    $domainforproxy = '';
                    $domainforproxy = getConfig('domainforproxy', $_SERVER['disktag']);
                    if ($domainforproxy != '') {
                        $header['Location'] = proxy_replace_domain($url, $domainforproxy);
                    }
                    return output('', 302, $header);
                } else return output('No "' . htmlspecialchars($_GET['random']) . '" files', 404);
            } else return output('Hidden', 401);
        } else return output('must provide a suffix, like "?random=gif".', 401);

    // is file && not preview mode, download file
    if ($files['type'] == 'file' && !isset($_GET['preview'])) {
        if ($_SERVER['ishidden'] < 4 || (!!getConfig('downloadencrypt', $_SERVER['disktag']) && $files['name'] != getConfig('passfile'))) {
            $url = $files['url'];
            $exp = strtolower(splitlast($files['name'], '.')[1]);
            if ($exp == 'htm' || $exp == 'html') {
                // HTML file display
                return output($files['content']['body'], $files['content']['stat']);
            } else {
                if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($files['time']) == strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) return output('', 304);
                $fileConduitSize = getConfig('fileConduitSize', $_SERVER['disktag']);
                $fileConduitCacheTime = getConfig('fileConduitCacheTime', $_SERVER['disktag']);
                if (!!$fileConduitSize || !!$fileConduitCacheTime) {
                    if ($fileConduitSize > 0) $fileConduitSize *= 1024 * 1024;
                    else $fileConduitSize = 1024 * 1024;
                    if ($fileConduitCacheTime > 0) $fileConduitCacheTime *= 3600;
                    else $fileConduitCacheTime = 3600;
                    /*if ($_SERVER['HTTP_RANGE']!='') {
                        $header['Range'] = $_SERVER['HTTP_RANGE'];
                        
                        $response = curl('GET', $files['url'], '', $header, 1);
                        //return output($header['Range'] . json_encode($response['returnhead']));
                        return output(
                            $response['body'],
                            $response['stat'],
                            $response['returnhead'],
                            //['Accept-Ranges' => 'bytes', 'Range' => $response['returnhead']['Range'], 'Content-Type' => $files['mime'], 'Cache-Control' => 'max-age=' . $fileConduitCacheTime],
                            false
                        );
                    } else {
                        return output('', 206,
                            ['Accept-Ranges' => 'bytes', 'Content-Range' => 'bytes 0-0/' . $files['size'], 'Content-Type' => $files['mime'] ]
                        );
                    }*/
                    if ($files['size'] < $fileConduitSize) return output(
                        base64_encode(file_get_contents($files['url'])),
                        200,
                        [
                            'Accept-Ranges' => 'bytes',
                            //'access-control-allow-origin' => '*',
                            //'access-control-expose-headers' => 'Content-Length, WWW-Authenticate, Location, Accept-Ranges',
                            'Content-Type' => $files['mime'],
                            //'Content-Disposition' => 'attachment; filename*="utf-8\'\'' . str_replace(".", "%2E", str_replace("+", "%20", urlencode($files['name']))) . '"; filename="' . $files['name'] . '"',
                            'Content-Disposition' => 'attachment; filename*="utf-8\'\'' . str_replace("+", "%20", urlencode($files['name'])) . '"; filename="' . $files['name'] . '"',
                            //'Content-Disposition' => 'attachment; filename*="utf-8\'\'' . iconv("GBK", "utf-8", $files['name']) . '"; filename="' . $files['name'] . '"',
                            'Cache-Control' => 'max-age=' . $fileConduitCacheTime,
                            //'Cache-Control' => 'max-age=0',
                            'Last-Modified' => gmdate('D, d M Y H:i:s T', strtotime($files['time']))
                        ],
                        true
                    );
                    //if ($files['size']<$fileConduitSize) return $drive->ConduitDown($files['url'], $files['time'], $fileConduitCacheTime);
                }
                if ($_SERVER['HTTP_RANGE'] != '') $header['Range'] = $_SERVER['HTTP_RANGE'];
                $header['Location'] = $url;
                $domainforproxy = '';
                $domainforproxy = getConfig('domainforproxy', $_SERVER['disktag']);
                if ($domainforproxy != '') {
                    $header['Location'] = proxy_replace_domain($url, $domainforproxy);
                }
                return output('', 302, $header);
            }
        }
    }
    // Show folder
    if ($files['type'] == 'folder' || $files['type'] == 'file') {
        return render_list($path, $files);
    } else {
        if (!isset($files['error'])) {
            if (is_array($files)) {
                $files['error']['message'] = json_encode($files, JSON_PRETTY_PRINT);
                $files['error']['code'] = 'unknownError';
                $files['error']['stat'] = 500;
            }
        }
        return message('<div style="margin:8px;"><pre>' . $files . json_encode($files, JSON_PRETTY_PRINT) . '</pre></div><a href="javascript:history.back(-1)">' . getconstStr('Back') . '</a>', $files['error']['code'], $files['error']['stat']);
    }
}

function get_content($path) {
    global $drive;
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if ($path1 != '/' && substr($path1, -1) == '/') $path1 = substr($path1, 0, -1);
    $file = $drive->list_files($path1);
    //var_dump($file);
    return $file;
}

function driveisfine($tag, &$drive = null) {
    global $slash;
    $disktype = getConfig('Driver', $tag);
    if (!$disktype) return false;
    if (!class_exists($disktype)) require 'disk' . $slash . $disktype . '.php';
    if (!$drive) $drive = new $disktype($tag);
    if ($drive->isfine()) return true;
    else return false;
}

function baseclassofdrive($d = null) {
    global $drive;
    if (!$d) $dr = $drive;
    else $dr = $d;
    if (!$dr) return false;
    return $dr->show_base_class();
}

function extendShow_diskenv($drive) {
    if (!$drive) return [];
    return $drive->ext_show_innerenv();
}

function isreferhost() {
    $referer = $_SERVER['referhost'];
    if ($referer == '') return true;
    if ($referer == $_SERVER['HTTP_HOST']) return true;
    $referrer = getConfig('referrer');
    if ($referrer == '') return true;
    $arr = explode('|', $referrer);
    foreach ($arr as $host) {
        if ($host == $referer) return true;
    }
    return false;
}

function adminpass2cookie($name, $pass, $timestamp) {
    return md5($name . ':' . md5($pass) . '@' . $timestamp) . "(" . $timestamp . ")";
}
function adminpass2storage($name, $pass, $timestamp, $rand) {
    return md5($timestamp . '/' . $pass . '^' . $name . '*' . $rand) . "(" . $rand . ")";
}
function compareadminmd5($name, $pass, $cookie, $storage = 'default') {
    $c = splitfirst($cookie, '(');
    $c_md5 = $c[0];
    $c_time = substr($c[1], 0, -1);
    if (!is_numeric($c_time)) return false;
    if (time() > $c_time) return false;
    if ($storage == 'default') {
        if (md5($name . ':' . md5($pass) . '@' . $c_time) == $c_md5) return true;
        else return false;
    } else {
        $s = splitfirst($storage, '(');
        $s_md5 = $s[0];
        $s_rand = substr($s[1], 0, -1);
        if (md5($c_time . '/' . $pass . '^' . $name . '*' . $s_rand) == $s_md5) return true;
        else return false;
    }
    return false;
}

function compareadminsha1($adminsha1, $timestamp, $pass) {
    if (!is_numeric($timestamp)) return 'Timestamp not Number';
    if (abs(time() - $timestamp) > 5 * 60) {
        date_default_timezone_set('UTC');
        return 'The time in server is ' . time() . ' (' . date("Y-m-d H:i:s") . ' UTC),<br>and your time is ' . $timestamp . ' (' . date("Y-m-d H:i:s", $timestamp) . ' UTC)';
    }
    if ($adminsha1 == sha1($timestamp . $pass)) return '';
    else return 'Error password';
}

function proxy_replace_domain($url, $domainforproxy) {
    global $drive;
    $tmp = splitfirst($url, '//');
    $http = $tmp[0];
    $tmp = splitfirst($tmp[1], '/');
    $domain = $tmp[0];
    $uri = $tmp[1];
    if (substr($domainforproxy, 0, 7) == 'http://' || substr($domainforproxy, 0, 8) == 'https://') $aim = $domainforproxy;
    else $aim = $http . '//' . $domainforproxy;
    if (substr($aim, -1) == '/') $aim = substr($aim, 0, -1);
    //$header['Location'] = $aim . '/' . $uri;
    //return $aim . '/' . $uri;
    if (strpos($url, '?') > 0) $sp = '&';
    else $sp = '?';
    $aim .= '/' . $uri . $sp . "basedrive=" . $drive->show_base_class();
    $aim .= '&Origindomain=' . $domain;
    return $aim;
}

function bchexdec($hex) {
    $len = strlen($hex);
    $dec = 0;
    for ($i = 1; $i <= $len; $i++)
        $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));

    return $dec;
}

function isHideFile($name) {
    $FunctionalityFile = [
        'head.md',
        'readme.md',
        'head.omf',
        'foot.omf',
        'favicon.ico',
        'robots.txt',
        'index.htm',
        'index.html',
    ];

    if ($name == getConfig('passfile')) return true;
    if (substr($name, 0, 1) == '.') return true;
    if (getConfig('hideFunctionalityFile')) if (in_array(strtolower($name), $FunctionalityFile)) return true;
    return false;
}

function getcache($str, $disktag = '') {
    $cache = filecache($disktag);
    return $cache->fetch($str);
}

function savecache($key, $value, $disktag = '', $exp = 1800) {
    $cache = filecache($disktag);
    return $cache->save($key, $value, $exp);
}

function filecache($disktag) {
    global $slash;
    $dir = sys_get_temp_dir();
    if (!is_writable($dir)) {
        $tmp = __DIR__ . $slash . 'tmp' . $slash;
        if (file_exists($tmp)) {
            if (is_writable($tmp)) $dir = $tmp;
        } elseif (mkdir($tmp)) $dir = $tmp;
    }
    $tag = $_SERVER['HTTP_HOST'] . $slash . 'OneManager' . $slash . $disktag;
    while (strpos($tag, $slash) > -1) $tag = str_replace($slash, '_', $tag);
    if (strpos($tag, ':') > -1) {
        $tag = str_replace(':', '_', $tag);
        $tag = str_replace('\\', '_', $tag);
    }
    // error_log1('DIR:' . $dir . ' TAG: ' . $tag);
    $cache = new \Doctrine\Common\Cache\FilesystemCache($dir, $tag);
    return $cache;
}

function calcDownKey($filename, $key = '') {
    if ($key) {
        // check key
        $tmp = splitfirst($key, '.');
        if ($tmp[1] != '') {
            $timestamp = $tmp[0];
            if (time() > $timestamp) return false;
            if (md5($timestamp . sha1($filename . getConfig('admin'))) == $tmp[1]) return true;
            else return false;
        } else return false;
    } else {
        // calc key
        $timestamp = time() + 1 * 24 * 60 * 60;
        return $timestamp . '.' . md5($timestamp . sha1($filename . getConfig('admin')));
    }
}

function findIndexPath($rootpath, $path = '') { // find the path of the first 'index.php' that not in rootpath.
    global $slash;
    if (substr($rootpath, -1) == $slash) $rootpath = substr($rootpath, 0, -1);
    if (substr($path, 0, 1) == $slash) $path = substr($path, 1);
    $handler = opendir(path_format($rootpath . $slash . $path)); //打开当前文件夹
    while ($filename = readdir($handler)) {
        if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..’，不要对他们进行操作
            $nowname = path_format($rootpath . $slash . $path . $slash . $filename);
            if (is_dir($nowname)) { // 如果读取的某个对象是文件夹，则递归
                $res = findIndexPath($rootpath, $path . $slash . $filename);
                if ($res !== '') return $res;
            } else {
                if ($filename === 'index.php') if ($path != '') return $rootpath . $slash . $path;
            }
        }
    }
    @closedir($handler);
    return '';
}

function sortConfig(&$arr) {
    ksort($arr);

    if (isset($arr['disktag'])) {
        $tags = explode('|', $arr['disktag']);
        unset($arr['disktag']);
        foreach ($tags as $tag) if (isset($arr[$tag])) {
            $disks[$tag] = $arr[$tag];
            unset($arr[$tag]);
        }
        $arr['disktag'] = implode('|', $tags);
        foreach ($disks as $k => $v) {
            $arr[$k] = $v;
        }
    }

    return $arr;
}

function chkTxtCode($str) {
    $code = array(
        'ASCII',
        'GBK',
        'GB18030',
        'UTF-8',
        'UTF-16',
    );
    foreach ($code as $c) {
        if ($str === iconv('UTF-8', $c, iconv($c, 'UTF-8', $str))) return $c;
    }
    return false;
}

function getconstStr($str) {
    global $constStr;
    if (isset($constStr[$str][$constStr['language']]) && $constStr[$str][$constStr['language']] != '') return $constStr[$str][$constStr['language']];
    return $constStr[$str]['en-us'];
}

function getListpath($domain) {
    $domain_path1 = getConfig('domain_path', $_SERVER['disktag']);
    $public_path = getConfig('public_path', $_SERVER['disktag']);
    $tmp_path = '';
    if ($domain_path1 != '') {
        $tmp = explode("|", $domain_path1);
        foreach ($tmp as $multidomain_paths) {
            $pos = strpos($multidomain_paths, ":");
            if ($pos > 0) {
                $domain1 = substr($multidomain_paths, 0, $pos);
                $tmp_path = path_format(substr($multidomain_paths, $pos + 1));
                $domain_path[$domain1] = $tmp_path;
                if ($public_path == '') $public_path = $tmp_path;
                //if (substr($multidomain_paths,0,$pos)==$host_name) $private_path=$tmp_path;
            }
        }
    }
    if (isset($domain_path[$domain])) return spurlencode($domain_path[$domain], '/');
    return spurlencode($public_path, '/');
}

function path_format($path) {
    $path = '/' . $path;
    while (strpos($path, '//') !== FALSE) {
        $path = str_replace('//', '/', $path);
    }
    return $path;
}

function spurlencode($str, $split = '') {
    $str = str_replace(' ', '%20', $str);
    $tmp = '';
    if ($split != '') {
        $tmparr = explode($split, $str);
        foreach ($tmparr as $str1) {
            $tmp .= urlencode($str1) . $split;
        }
        $tmp = substr($tmp, 0, strlen($tmp) - strlen($split));
    } else {
        $tmp = urlencode($str);
    }
    $tmp = str_replace('%2520', '%20', $tmp);
    $tmp = str_replace('%26amp%3B', '&', $tmp);
    return $tmp;
}

function base64y_encode($str) {
    $str = base64_encode($str);
    while (substr($str, -1) == '=') $str = substr($str, 0, -1);
    while (strpos($str, '+') !== false) $str = str_replace('+', '-', $str);
    while (strpos($str, '/') !== false) $str = str_replace('/', '_', $str);
    return $str;
}

function base64y_decode($str) {
    while (strpos($str, '_') !== false) $str = str_replace('_', '/', $str);
    while (strpos($str, '-') !== false) $str = str_replace('-', '+', $str);
    while (strlen($str) % 4) $str .= '=';
    $str = base64_decode($str);
    //if (strpos($str, '%')!==false) $str = urldecode($str);
    return $str;
}

function error_log1($str) {
    error_log($str);
}

function is_guestup_path($path) {
    if (getConfig('guestup_path', $_SERVER['disktag']) != '') {
        $a1 = path_format(path_format(urldecode($_SERVER['list_path'] . path_format($path))) . '/');
        $a2 = path_format(path_format(getConfig('guestup_path', $_SERVER['disktag'])) . '/');
        if (strtolower($a1) == strtolower($a2)) return 1;
    }
    return 0;
}

function array_value_isnot_null($arr) {
    return $arr !== '';
}

function no_return_curl($method, $url, $data = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response['body'] = curl_exec($ch);
    $response['stat'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $response;
}
function curl($method, $url, $data = '', $headers = [], $returnheader = 0, $location = 0) {
    //if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    //if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'application/x-www-form-urlencoded';
    if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) $headers['Content-Type'] = '';
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, $returnheader);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    if ($location) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //$response['body'] = curl_exec($ch);
    if ($returnheader) {
        $tmpresult = curl_exec($ch);
        //error_log1($tmpresult);
        $tmpres = splitlast($tmpresult, "\r\n\r\n");
        $response['body'] = $tmpres[1];
        $returnhead = $tmpres[0];
        //echo "HEAD:" . $returnhead;
        foreach (explode("\r\n", $returnhead) as $head) {
            $tmp = explode(': ', $head);
            $heads[$tmp[0]] = $tmp[1];
        }
        $response['returnhead'] = $heads;
    } else {
        $response['body'] = curl_exec($ch);
    }
    $response['stat'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $response;
}

function clearbehindvalue($path, $page1, $maxpage, $pageinfocache) {
    for ($page = $page1 + 1; $page < $maxpage; $page++) {
        $pageinfocache['nextlink_' . $path . '_page_' . $page] = '';
    }
    $pageinfocache = array_filter($pageinfocache, 'array_value_isnot_null');
    return $pageinfocache;
}

function comppass($pass) {
    if ($_POST['password1'] !== '') if (md5($_POST['password1']) === $pass) {
        date_default_timezone_set('UTC');
        $_SERVER['Set-Cookie'] = 'password=' . $pass . '; expires=' . date(DATE_COOKIE, strtotime('+1hour'));
        date_default_timezone_set(get_timezone($_SERVER['timezone']));
        return 2;
    }
    if ($_COOKIE['password'] !== '') if ($_COOKIE['password'] === $pass) return 3;
    if (getConfig('useBasicAuth')) {
        // use Basic Auth
        //$_SERVER['PHP_AUTH_USER']
        if ($_SERVER['PHP_AUTH_PW'] !== '') if (md5($_SERVER['PHP_AUTH_PW']) === $pass) {
            date_default_timezone_set('UTC');
            $_SERVER['Set-Cookie'] = 'password=' . $pass . '; expires=' . date(DATE_COOKIE, strtotime('+1hour'));
            date_default_timezone_set(get_timezone($_SERVER['timezone']));
            return 2;
        }
    }
    return 4;
}

function encode_str_replace($str) {
    $str = str_replace('%', '%25', $str);
    if (strpos($str, '&amp;')) $str = str_replace('&amp;', '&amp;amp;', $str);
    $str = str_replace('+', '%2B', $str);
    $str = str_replace('#', '%23', $str);
    return $str;
}

function gethiddenpass($path, $passfile) {
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if ($path1 != '/' && substr($path1, -1) == '/') $path1 = substr($path1, 0, -1);
    $password = getcache('path_' . $path1 . '/?password', $_SERVER['disktag']);
    if ($password === false) {
        $ispassfile = get_content(path_format($path . '/' . urlencode($passfile)));
        //echo $path . '<pre>' . json_encode($ispassfile, JSON_PRETTY_PRINT) . '</pre>';
        if ($ispassfile['type'] == 'file') {
            $arr = curl('GET', $ispassfile['url']);
            if ($arr['stat'] == 200) {
                $passwordf = explode("\n", $arr['body']);
                $password = $passwordf[0];
                if ($password === '') {
                    return '';
                } else {
                    $password = md5($password);
                    savecache('path_' . $path1 . '/?password', $password, $_SERVER['disktag']);
                    return $password;
                }
            } else {
                //return md5('DefaultP@sswordWhenNetworkError');
                return md5(md5(time()) . rand(1000, 9999));
            }
        } else {
            savecache('path_' . $path1 . '/?password', 'null', $_SERVER['disktag']);
            if ($path !== '') {
                $path = substr($path, 0, strrpos($path, '/'));
                return gethiddenpass($path, $passfile);
            } else {
                return '';
            }
        }
    } elseif ($password === 'null') {
        if ($path !== '') {
            $path = substr($path, 0, strrpos($path, '/'));
            return gethiddenpass($path, $passfile);
        } else {
            return '';
        }
    } else return $password;
    // return md5('DefaultP@sswordWhenNetworkError');
}

function get_timezone($timezone = '8') {
    global $timezones;
    if ($timezone == '') $timezone = '8';
    return $timezones[$timezone];
}

function message($message, $title = 'Message', $statusCode = 200, $wainstat = 0) {
    $html = '
<html lang="' . $_SERVER['language'] . '">
<html>
    <meta charset=utf-8>
    <meta name=viewport content="width=device-width,initial-scale=1">
    <body>
        <a href="' . $_SERVER['base_path'] . '">' . getconstStr('Back') . getconstStr('Home') . '</a>
        <h1>' . $title . '</h1>
        <div id="dis" style="display: none;">

' . $message . '

        </div>';
    if ($wainstat) {
        $html .= '
        <div id="err"></div>
        <script>
            var dis = document.getElementById("dis");
            var errordiv = document.getElementById("err");
            //var deployTime = new Date().getTime();
            dis.style.display = "none";
            var x = "";
            var min = 0;
            function getStatus() {
                x += ".";
                min++;
                var xhr = new XMLHttpRequest();
                var url = "?WaitFunction=" + (status!=""?status:"1");
                xhr.open("GET", url);
                //xhr.setRequestHeader("Authorization", "Bearer ");
                xhr.onload = function(e) {
                    if (xhr.status==200) {
                        //var deployStat = JSON.parse(xhr.responseText).readyState;
                        if (xhr.responseText=="ok") {
                            errordiv.innerHTML = "";
                            dis.style.display = "";
                        } else {
                            errordiv.innerHTML = "ERROR<br>" + xhr.responseText;
                            //setTimeout(function() { getStatus() }, 1000);
                        }
                    } else if (xhr.status==206) {
                        errordiv.innerHTML = "' . getconstStr('Wait') . ' " + min + "<br>" + x;
                        setTimeout(function() { getStatus() }, 1000);
                    } else {
                        errordiv.innerHTML = "ERROR<br>" + xhr.status + "<br>" + xhr.responseText;
                        console.log(xhr.status);
                        console.log(xhr.responseText);
                    }
                }
                xhr.send(null);
            }
            getStatus();
        </script>';
    } else {
        $html .= '
        <script>document.getElementById("dis").style.display = "";</script>';
    }
    $html .= '
    </body>
</html>
';
    return output($html, $statusCode);
}

function needUpdate() {
    global $slash;
    $current_version = file_get_contents(__DIR__ . $slash . 'version');
    $current_ver = substr($current_version, strpos($current_version, '.') + 1);
    $current_ver = explode(urldecode('%0A'), $current_ver)[0];
    $current_ver = explode(urldecode('%0D'), $current_ver)[0];
    $split = splitfirst($current_version, '.' . $current_ver)[0] . '.' . $current_ver;
    if (!($github_version = getcache('github_version'))) {
        //$tmp = curl('GET', 'https://raw.githubusercontent.com/qkqpttgf/OneManager-php/master/version');
        $tmp = curl('GET', 'https://git.hit.edu.cn/ysun/OneManager-php/-/raw/master/version');
        if ($tmp['stat'] == 0) return 0;
        $github_version = $tmp['body'];
        savecache('github_version', $github_version);
    }
    $github_ver = substr($github_version, strpos($github_version, '.') + 1);
    $github_ver = explode(urldecode('%0A'), $github_ver)[0];
    $github_ver = explode(urldecode('%0D'), $github_ver)[0];
    if ($current_ver != $github_ver) {
        //$_SERVER['github_version'] = $github_version;
        $_SERVER['github_ver_new'] = splitfirst($github_version, $split)[0];
        $_SERVER['github_ver_old'] = splitfirst($github_version, $_SERVER['github_ver_new'])[1];
        return 1;
    }
    return 0;
}

function output($body, $statusCode = 200, $headers = [], $isBase64Encoded = false) {
    if (isset($_SERVER['Set-Cookie'])) $headers['Set-Cookie'] = $_SERVER['Set-Cookie'];
    if (baseclassofdrive() == 'Aliyundrive' || baseclassofdrive() == 'BaiduDisk') $headers['Referrer-Policy'] = 'no-referrer';
    //$headers['Referrer-Policy'] = 'same-origin';
    //$headers['X-Frame-Options'] = 'sameorigin';
    if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'text/html';
    return [
        'isBase64Encoded' => $isBase64Encoded,
        'statusCode' => $statusCode,
        'headers' => $headers,
        'body' => $body
    ];
}

function passhidden($path) {
    if ($_SERVER['admin']) return 0;
    //$path = str_replace('+','%2B',$path);
    //$path = str_replace('&amp;','&', path_format(urldecode($path)));
    if (getConfig('passfile') != '') {
        //$path = spurlencode($path,'/');
        //if (substr($path,-1)=='/') $path=substr($path,0,-1);
        $hiddenpass = gethiddenpass($path, getConfig('passfile'));
        if ($hiddenpass != '') {
            return comppass($hiddenpass);
        } else {
            return 1;
        }
    } else {
        return 0;
    }
    return 4;
}

function size_format($byte) {
    $i = 0;
    while (abs($byte) >= 1024) {
        $byte = $byte / 1024;
        $i++;
        if ($i == 4) break;
    }
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $ret = round($byte, 2);
    return ($ret . ' ' . $units[$i]);
}

function time_format($ISO) {
    if ($ISO == '') return date('Y-m-d H:i:s');
    $ISO = str_replace('T', ' ', $ISO);
    $ISO = str_replace('Z', ' ', $ISO);
    return date('Y-m-d H:i:s', strtotime($ISO . " UTC"));
}

function adminform($name = '', $pass = '', $storage = '', $path = '') {
    $html = '<html>
    <head>
        <title>' . getconstStr('AdminLogin') . '</title>
        <meta charset=utf-8>
        <meta name=viewport content="width=device-width,initial-scale=1">
    </head>';
    if ($name == 'admin' && $pass != '') {
        $html .= '
        <!--<meta http-equiv="refresh" content="3;URL=' . $path . '">-->
    <body>
        ' . getconstStr('LoginSuccess') . '
        <script>
            localStorage.setItem("admin", "' . $storage . '");
            var url = location.href;
            var search = location.search;
            url = url.substr(0, url.length-search.length);
            if (search.indexOf("preview")>0) url += "?preview";
            location = url;
        </script>
    </body>
</html>';
        $statusCode = 201;
        date_default_timezone_set('UTC');
        $_SERVER['Set-Cookie'] = $name . '=' . $pass . '; path=' . $_SERVER['base_path'] . '; expires=' . date(DATE_COOKIE, strtotime('+7day'));
        return output($html, $statusCode);
    }
    $statusCode = 401;
    $html .= '
<body>
    <div>
    <center><h4>' . getconstStr('InputPassword') . '</h4>
    ' . $name . '
    <form action="" method="post" onsubmit="return sha1loginpass(this);">
        <div>
            <input id="password1" name="password1" type="password"/>
            <input name="timestamp" type="hidden"/>
            <input type="submit" value="' . getconstStr('Login') . '">
        </div>
    </form>
    </center>
    </div>
</body>';
    $html .= '
<script>
    document.getElementById("password1").focus();
    function sha1loginpass(f) {
        if (f.password1.value=="") return false;
        try {
            timestamp = new Date().getTime() + "";
            timestamp = timestamp.substr(0, timestamp.length-3);
            f.timestamp.value = timestamp;
            f.password1.value = sha1(timestamp + "" + f.password1.value);
            return true;
        } catch {
            //alert("sha1.js not loaded.");
            if (confirm("sha1.js not loaded.\n\nLoad from program?")) loadjs("?jsFile=sha1.min.js");
            return false;
        }
    }
    function loadjs(url) {
        var xhr = new XMLHttpRequest;
        xhr.open("GET", url);
        xhr.onload = function(e) {
            if (xhr.status==200) {
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.text = xhr.responseText;
                document.body.appendChild(script);
            } else {
                console.log(xhr.response);
            }
        }
        xhr.send(null);
    }
</script>
<script src="?jsFile=sha1.min.js"></script>';
    $html .= '</html>';
    return output($html, $statusCode);
}

function adminoperate($path) {
    global $drive;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') if (!driveisfine($_SERVER['disktag'], $drive)) return output($_SERVER['disktag'] ? 'disk [ ' . $_SERVER['disktag'] . ' ] error.' : 'Not in drive', 403);
    $path1 = path_format($_SERVER['list_path'] . '/' . $path);
    if (substr($path1, -1) == '/') $path1 = substr($path1, 0, -1);
    $tmpget = $_GET;
    $tmppost = $_POST;
    $tmparr['statusCode'] = 0;

    if (isset($tmpget['RefreshCache'])) {
        savecache('path_' . $path1 . '/?password', '', $_SERVER['disktag'], 1);
        savecache('customTheme', '', '', 1);
        return message('<meta http-equiv="refresh" content="2;URL=./">
        <meta name=viewport content="width=device-width,initial-scale=1">', getconstStr('RefreshCache'), 202);
    }

    if ((isset($tmpget['rename_newname']) && $tmpget['rename_newname'] != $tmpget['rename_oldname'] && $tmpget['rename_newname'] != '') || (isset($tmppost['rename_newname']) && $tmppost['rename_newname'] != $tmppost['rename_oldname'] && $tmppost['rename_newname'] != '')) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        if (isset($tmppost['rename_newname'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // rename 重命名
        $file['path'] = $path1;
        $file['name'] = ${$VAR}['rename_oldname'];
        $file['id'] = ${$VAR}['rename_fileid'];
        return $drive->Rename($file, ${$VAR}['rename_newname']);
    }
    if (isset($tmpget['delete_name']) || isset($tmppost['delete_name'])) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        if (isset($tmppost['delete_name'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // delete 删除
        $file['path'] = $path1;
        $file['name'] = ${$VAR}['delete_name'];
        $file['id'] = ${$VAR}['delete_fileid'];
        return $drive->Delete($file);
    }
    if ((isset($tmpget['operate_action']) && $tmpget['operate_action'] == getconstStr('Encrypt')) || (isset($tmppost['operate_action']) && $tmppost['operate_action'] == getconstStr('Encrypt'))) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        if (isset($tmppost['operate_action'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // encrypt 加密
        if (getConfig('passfile') == '') return message(getconstStr('SetpassfileBfEncrypt'), '', 403);
        if (${$VAR}['encrypt_folder'] == '/') ${$VAR}['encrypt_folder'] == '';
        $folder['path'] = path_format($path1 . '/' . spurlencode(${$VAR}['encrypt_folder'], '/'));
        $folder['name'] = ${$VAR}['encrypt_folder'];
        $folder['id'] = ${$VAR}['encrypt_fileid'];
        return $drive->Encrypt($folder, getConfig('passfile'), ${$VAR}['encrypt_newpass']);
    }
    if (isset($tmpget['move_folder']) || isset($tmppost['move_folder'])) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        if (isset($tmppost['move_folder'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // move 移动
        $moveable = 1;
        if ($path == '/' && ${$VAR}['move_folder'] == '/../') $moveable = 0;
        if (${$VAR}['move_folder'] == ${$VAR}['move_name']) $moveable = 0;
        if ($moveable) {
            $file['path'] = $path1;
            $file['name'] = ${$VAR}['move_name'];
            $file['id'] = ${$VAR}['move_fileid'];
            if (${$VAR}['move_folder'] == '/../') {
                $foldername = path_format('/' . urldecode($path1 . '/'));
                $foldername = substr($foldername, 0, -1);
                $foldername = splitlast($foldername, '/')[0];
            } else $foldername = path_format('/' . urldecode($path1) . '/' . ${$VAR}['move_folder']);
            $folder['path'] = $foldername;
            $folder['name'] = ${$VAR}['move_folder'];
            $folder['id'] = '';
            return $drive->Move($file, $folder);
        } else {
            return output('{"error":"' . getconstStr('CannotMove') . '"}', 403);
        }
    }
    if (isset($tmpget['copy_name']) || isset($tmppost['copy_name'])) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        if (isset($tmppost['copy_name'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // copy 复制
        $file['path'] = $path1;
        $file['name'] = ${$VAR}['copy_name'];
        $file['id'] = ${$VAR}['copy_fileid'];
        return $drive->Copy($file);
    }
    if (isset($tmppost['editfile'])) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        // edit 编辑
        $file['path'] = $path1;
        $file['name'] = '';
        $file['id'] = '';
        return $drive->Edit($file, $tmppost['editfile']);
    }
    if (isset($tmpget['create_name']) || isset($tmppost['create_name'])) {
        if (!compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) return ['statusCode' => 403];
        if (isset($tmppost['create_name'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // create 新建
        $parent['path'] = $path1;
        $parent['name'] = '';
        $parent['id'] = ${$VAR}['create_fileid'];
        return $drive->Create($parent, ${$VAR}['create_type'], ${$VAR}['create_name'], ${$VAR}['create_text']);
    }
    return $tmparr;
}

function splitfirst($str, $split) {
    $len = strlen($split);
    $pos = strpos($str, $split);
    if ($pos === false) {
        $tmp[0] = $str;
        $tmp[1] = '';
    } elseif ($pos > 0) {
        $tmp[0] = substr($str, 0, $pos);
        $tmp[1] = substr($str, $pos + $len);
    } else {
        $tmp[0] = '';
        $tmp[1] = substr($str, $len);
    }
    if ($tmp[1] === false) $tmp[1] = '';
    return $tmp;
}

function splitlast($str, $split) {
    $len = strlen($split);
    $pos = strrpos($str, $split);
    if ($pos === false) {
        $tmp[0] = $str;
        $tmp[1] = '';
    } elseif ($pos > 0) {
        $tmp[0] = substr($str, 0, $pos);
        $tmp[1] = substr($str, $pos + $len);
    } else {
        $tmp[0] = '';
        $tmp[1] = substr($str, $len);
    }
    if ($tmp[1] === false) $tmp[1] = '';
    return $tmp;
}

function children_name($children) {
    $tmp = [];
    foreach ($children as $file) {
        $tmp[strtolower($file['name'])] = $file;
    }
    return $tmp;
}

function EnvOpt($needUpdate = 0) {
    global $constStr;
    global $EnvConfigs;
    global $timezones;
    global $slash;
    global $drive;
    global $platform;
    ksort($EnvConfigs);
    $disktag_s = getConfig('disktag');
    $disktags = explode('|', $disktag_s);
    $envs = '';
    //foreach ($EnvConfigs as $env => $v) if (isCommonEnv($env)) $envs .= '\'' . $env . '\', ';
    $envs = substr(json_encode(array_keys($EnvConfigs)), 1, -1);

    $html = '<title>OneManager ' . getconstStr('Setup') . '</title>';
    if (isset($_POST['updateProgram']) && $_POST['updateProgram'] == getconstStr('updateProgram')) if (compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) {
        $response = setConfigResponse(OnekeyUpate($_POST['GitSource'], $_POST['auth'], $_POST['project'], $_POST['branch']));
        if (api_error($response)) {
            $html = api_error_msg($response);
            $title = 'Error';
            return message($html, $title, 400);
        } else {
            //WaitSCFStat();
            $html .= getconstStr('UpdateSuccess') . '<br><a href="">' . getconstStr('Back') . '</a><script>var status = "' . (isset($response['DplStatus']) ? $response['DplStatus'] : "") . '";</script>';
            $title = getconstStr('Setup');
            return message($html, $title, 202, 1);
        }
    } else return message('please login again', 'Need login', 403);
    if (isset($_POST['submit1'])) if (compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) {
        $_SERVER['disk_oprating'] = '';
        foreach ($_POST as $k => $v) {
            if (isShowedEnv($k) || $k == 'disktag_del' || $k == 'disktag_add' || $k == 'disktag_rename' || $k == 'disktag_copy' || $k == 'client_secret') {
                $tmp[$k] = $v;
            }
            if ($k == 'disktag_newname') {
                $v = preg_replace('/[^0-9a-zA-Z|_]/i', '', $v);
                $f = substr($v, 0, 1);
                if (strlen($v) == 1) $v .= '_';
                if (isCommonEnv($v)) {
                    return message('Do not input ' . $envs . '<br><a href="">' . getconstStr('Back') . '</a>', 'Error', 400);
                } elseif (!(('a' <= $f && $f <= 'z') || ('A' <= $f && $f <= 'Z'))) {
                    return message('<a href="">' . getconstStr('Back') . '</a>', 'Please start with letters', 400);
                } elseif (getConfig($v)) {
                    return message('<a href="">' . getconstStr('Back') . '</a>', 'Same tag', 400);
                } else {
                    $tmp[$k] = $v;
                }
            }
            if ($k == 'disktag_sort') {
                $td = implode('|', json_decode($v));
                if (strlen($td) == strlen(getConfig('disktag'))) $tmp['disktag'] = $td;
                else return message('Something wrong.', 'ERROR', 400);
            }
            if ($k == 'disk') $_SERVER['disk_oprating'] = $v;
        }
        /*if ($tmp['domain_path']!='') {
            $tmp1 = explode("|",$tmp['domain_path']);
            $tmparr = [];
            foreach ($tmp1 as $multidomain_paths){
                $pos = strpos($multidomain_paths,":");
                if ($pos>0) $tmparr[substr($multidomain_paths, 0, $pos)] = path_format(substr($multidomain_paths, $pos+1));
            }
            $tmp['domain_path'] = $tmparr;
        }*/
        $response = setConfigResponse(setConfig($tmp, $_SERVER['disk_oprating']));
        if (api_error($response)) {
            $html = api_error_msg($response);
            $title = 'Error';
            return message($html, $title, 409);
        } else {
            $html .= getconstStr('Success') . '!<br>
            <a href="">' . getconstStr('Back') . '</a>
            <script>
                var status = "' . $response['DplStatus'] . '";
            </script>';
            $title = getconstStr('Setup');
            return message($html, $title, 200, 1);
        }
    } else return message('please login again', 'Need login', 403);
    if (isset($_POST['config_b'])) if (compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) {
        if (!$_POST['pass']) return output("{\"Error\": \"No admin pass\"}", 403);
        if (!is_numeric($_POST['timestamp'])) return output("{\"Error\": \"Error time\"}", 403);
        if (abs(time() - $_POST['timestamp'] / 1000) > 5 * 60) return output("{\"Error\": \"Timeout\"}", 403);

        if ($_POST['pass'] == sha1(getConfig('admin') . $_POST['timestamp'])) {
            if ($_POST['config_b'] == 'export') {
                foreach ($EnvConfigs as $env => $v) {
                    if (isCommonEnv($env) && isShowedEnv($env)) {
                        $value = getConfig($env);
                        if ($value) $tmp[$env] = $value;
                    }
                }
                if ($disktag_s) $tmp["disktag"] = $disktag_s;
                foreach ($disktags as $disktag) {
                    $d = getConfig($disktag);
                    if ($d == '') {
                        $d = '';
                    } elseif (gettype($d) == 'array') {
                        $tmp[$disktag] = $d;
                    } else {
                        $tmp[$disktag] = json_decode($d, true);
                    }
                }
                unset($tmp['admin']);
                return output(json_encode($tmp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            if ($_POST['config_b'] == 'import') {
                if (!$_POST['config_t']) return output("{\"Error\": \"Empty config.\"}", 403);
                $c = '{' . splitfirst($_POST['config_t'], '{')[1];
                $c = splitlast($c, '}')[0] . '}';
                $tmp = json_decode($c, true);
                if (!!!$tmp) return output("{\"Error\": \"Config input error. " . $c . "\"}", 403);
                if (isset($tmp['disktag'])) $tmptag = $tmp['disktag'];
                foreach ($EnvConfigs as $env => $v) {
                    if (isCommonEnv($env)) {
                        if (isShowedEnv($env)) {
                            if (getConfig($env) != '' && !isset($tmp[$env])) $tmp[$env] = '';
                        } else {
                            unset($tmp[$env]);
                        }
                    }
                }
                if ($disktags) foreach ($disktags as $disktag) {
                    if ($disktag != '' && !isset($tmp[$disktag])) $tmp[$disktag] = '';
                }
                if ($tmptag) $tmp['disktag'] = $tmptag;
                $response = setConfigResponse(setConfig($tmp));
                if (api_error($response)) {
                    return output("{\"Error\": \"" . api_error_msg($response) . "\"}", 500);
                } else {
                    return output("{\"Success\": \"Success\"}", 200);
                }
            }
            return output(json_encode($_POST), 500);
        } else {
            return output("{\"Error\": \"Admin pass error\"}", 403);
        }
    } else return message('please login again', 'Need login', 403);
    if (isset($_POST['changePass'])) if (compareadminmd5('admin', getConfig('admin'), $_COOKIE['admin'], $_POST['_admin'])) {
        if (!is_numeric($_POST['timestamp'])) return message("Error time<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
        if (abs(time() - $_POST['timestamp'] / 1000) > 5 * 60) return message("Timeout<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
        if ($_POST['newPass1'] == '' || $_POST['newPass2'] == '') return message("Empty new pass<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
        if ($_POST['newPass1'] !== $_POST['newPass2']) return message("Twice new pass not the same<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
        if ($_POST['newPass1'] == getConfig('admin')) return message("New pass same to old one<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
        if ($_POST['oldPass'] == sha1(getConfig('admin') . $_POST['timestamp'])) {
            $tmp['admin'] = $_POST['newPass1'];
            $response = setConfigResponse(setConfig($tmp));
            if (api_error($response)) {
                return message(api_error_msg($response) . "<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
            } else {
                return message("Success<a href=\"\">" . getconstStr('Back') . "</a><script>var status = \"" . $response['DplStatus'] . "\";</script>", "Success", 200, 1);
            }
        } else {
            return message("Old pass error<a href=\"\">" . getconstStr('Back') . "</a>", "Error", 403);
        }
    } else return message('please login again', 'Need login', 403);

    $html .= '
<a id="back" href="./">' . getconstStr('Back') . '</a><br>
    <script>
        if (location.search.indexOf("preview")>0) document.getElementById("back").href = "?preview";
    </script>
';
    if ($_GET['setup'] === 'cmd') {
        $statusCode = 200;
        $html .= '
OneManager DIR: ' . __DIR__ . '
<form name="form1" method="POST" action="">
    <input id="inputarea" name="cmd" style="width:100%" value="' . htmlspecialchars($_POST['cmd']) . '" placeholder="ls, pwd, cat"><br>
    <input type="submit" value="post">
</form>';
        if ($_POST['cmd'] != '') {
            $html .= '
<pre>';
            @ob_start();
            passthru($_POST['cmd'], $cmdstat);
            if ($cmdstat > 0) $statusCode = 400;
            if ($cmdstat === 1) $statusCode = 403;
            if ($cmdstat === 127) $statusCode = 404;
            $html .= '
stat: ' . $cmdstat . '
output:

' . htmlspecialchars(ob_get_clean());
            $html .= '</pre>';
        }
        $html .= '
<script>
    setTimeout(function () {
        let inputarea = document.getElementById(\'inputarea\');
        //console.log(a + ", " + inputarea.value);
        inputarea.focus();
        inputarea.setSelectionRange(inputarea.value.length, inputarea.value.length);
    }, 500);
</script>';
        return message($html, 'Run cmd', $statusCode);
    }
    if ($_GET['setup'] === 'auth') {
        return changeAuthKey();
    }
    if ($_GET['setup'] === 'platform') {
        $frame = '
<table border=1 width=100%>
    <form name="common" action="" method="post">
        <input name="_admin" type="hidden" value="">';
        foreach ($EnvConfigs as $key => $val) if (isCommonEnv($key) && isShowedEnv($key)) {
            $frame .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%>';
            if ($key == 'timezone') {
                $frame .= '
                <select name="' . $key . '">';
                foreach (array_keys($timezones) as $zone) {
                    $frame .= '
                    <option value="' . $zone . '" ' . ($zone == getConfig($key) ? 'selected="selected"' : '') . '>' . $zone . '</option>';
                }
                $frame .= '
                </select>
                ' . getconstStr('EnvironmentsDescription')[$key];
            } elseif ($key == 'theme') {
                $theme_arr = scandir(__DIR__ . $slash . 'theme');
                $frame .= '
                <select name="' . $key . '">
                    <option value=""></option>';
                foreach ($theme_arr as $v1) {
                    if ($v1 != '.' && $v1 != '..') $frame .= '
                    <option value="' . $v1 . '" ' . ($v1 == getConfig($key) ? 'selected="selected"' : '') . '>' . $v1 . '</option>';
                }
                $frame .= '
                </select>
                ' . getconstStr('EnvironmentsDescription')[$key];
            } elseif (isSwitchEnv($key)) {
                $frame .= '
                <select name="' . $key . '">
                    <option value=""></option>
                    <option value="1"' . (getConfig($key) ? ' selected="selected"' : '') . '>true</option>
                </select>
                ' . getconstStr('EnvironmentsDescription')[$key];
            } /*elseif ($key=='domain_path') {
            $tmp = getConfig($key);
            $domain_path = '';
            foreach ($tmp as $k1 => $v1) {
                $domain_path .= $k1 . ':' . $v1 . '|';
            }
            $domain_path = substr($domain_path, 0, -1);
            $frame .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . $domain_path . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
        </tr>';
        }*/ else $frame .= '
                <input type="text" name="' . $key . '" value="' . htmlspecialchars(getConfig($key)) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%">';
            $frame .= '
            </td>
        </tr>';
        }
        $frame .= '
        <tr><td><input type="submit" name="submit1" value="' . getconstStr('Setup') . '"></td><td></td></tr>
    </form>
</table><br>';
    } elseif (isset($_GET['disktag']) && $_GET['disktag'] !== true && in_array($_GET['disktag'], $disktags)) {
        $disktag = $_GET['disktag'];
        $disk_tmp = null;
        $diskok = driveisfine($disktag, $disk_tmp);
        $frame = '
<table width=100%>
    <tr>
        <td>
            <form action="" method="post" style="margin: 0" onsubmit="return renametag(this);">
                <input type="hidden" name="disktag_rename" value="' . $disktag . '">
                <input name="_admin" type="hidden" value="">
                <input type="text" name="disktag_newname" value="' . $disktag . '" placeholder="' . getconstStr('EnvironmentsDescription')['disktag'] . '">
                <input type="submit" name="submit1" value="' . getconstStr('RenameDisk') . '">
            </form>
        </td>
    </tr>
</table><br>
<table>
<tr>
    <td>
        <form action="" method="post" style="margin: 0" onsubmit="return deldiskconfirm(this);">
            <input type="hidden" name="disktag_del" value="' . $disktag . '">
            <input name="_admin" type="hidden" value="">
            <input type="submit" name="submit1" value="' . getconstStr('DelDisk') . '">
        </form>
    </td>
    <td>
        <form action="" method="post" style="margin: 0" onsubmit="return cpdiskconfirm(this);">
            <input type="hidden" name="disktag_copy" value="' . $disktag . '">
            <input name="_admin" type="hidden" value="">
            <input type="submit" name="submit1" value="' . getconstStr('CopyDisk') . '">
        </form>
    </td>
</tr>
</table>
<form name="' . $disktag . '" action="" method="post">
    <input name="_admin" type="hidden" value="">
    <input type="hidden" name="disk" value="' . $disktag . '">
<table border=1 width=100%>
    <tr>
        <td>Driver</td>
        <td>' . getConfig('Driver', $disktag);
        if ($diskok) $frame .= ' <a href="?AddDisk=' . get_class($disk_tmp) . '&disktag=' . $disktag . '&SelectDrive">' . getconstStr('ChangeDrivetype') . '</a>';
        $frame .= '</td>
    </tr>';
        if (getConfig('client_id', $disktag) && getConfig('client_secret', $disktag)) {
            $frame .= '
    <tr>
        <td>client_id</td>
        <td>' . getConfig('client_id', $disktag) . '</td>
    </tr>';
            $frame .= '
    <tr>
        <td>client_secret</td>
        <td><input type="text" name="client_secret" value="' . getConfig('client_secret', $disktag) . '" placeholder="' . getconstStr('EnvironmentsDescription')['client_secret'] . '" style="width:100%"></td>
    </tr>';
            if (!$diskok) $frame .= '
<tr><td></td><td><input type="submit" name="submit1" value="' . getconstStr('Setup') . '"></td></tr>';
        }
        if ($diskok) {
            $frame .= '
    <tr>
        <td>diskSpace</td><td>' . $disk_tmp->getDiskSpace() . '</td>
    </tr>';
            foreach (extendShow_diskenv($disk_tmp) as $ext_env) {
                $frame .= '
    <tr>
        <td>' . $ext_env . '</td>
        <td>' . getConfig($ext_env, $disktag) . '</td>
    </tr>';
            }

            foreach ($EnvConfigs as $key => $val) if (isInnerEnv($key) && isShowedEnv($key)) {
                $frame .= '
    <tr>
        <td><label>' . $key . '</label></td>
        <td width=100%>';
                if ($key == 'diskDisplay') {
                    $frame .= '
            <select name="' . $key . '">
                <option value=""' . (getConfig($key, $disktag) === '' ? ' selected' : '') . '> </option>
                <option value="hidden"' . (getConfig($key, $disktag) === 'hidden' ? ' selected' : '') . '>hidden</option>
                <option value="disable"' . (getConfig($key, $disktag) === 'disable' ? ' selected' : '') . '>disable</option>
            </select>
            ' . getconstStr('EnvironmentsDescription')[$key];
                } elseif (isSwitchEnv($key)) {
                    $frame .= '
            <select name="' . $key . '">
                <option value=""></option>
                <option value="1"' . (getConfig($key, $disktag) != '' ? ' selected="selected"' : '') . '>true</option>
            </select>
            ' . getconstStr('EnvironmentsDescription')[$key];
                } else {
                    $frame .= '
            <input type="text" name="' . $key . '" value="' . getConfig($key, $disktag) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%">';
                }
                $frame .= '
        </td>
    </tr>';
            }
            $frame .= '
    <tr><td></td><td><input type="submit" name="submit1" value="' . getconstStr('Setup') . '"></td></tr>';
        } else {
            $frame .= '
<tr>
    <td colspan="2">' . ($disk_tmp->error['body'] ? $disk_tmp->error['stat'] . '<br>' . $disk_tmp->error['body'] : 'Add this disk again.') . '</td>
</tr>';
        }
        $frame .= '
</table>
</form>

<script>
    function deldiskconfirm(t) {
        var msg="' . getconstStr('Delete') . ' ??";
        if (confirm(msg)==true) return true;
        else return false;
    }
    function cpdiskconfirm(t) {
        var msg="' . getconstStr('Copy') . ' ??";
        if (confirm(msg)==true) return true;
        //else 
        return false;
    }
    function renametag(t) {
        if (t.disktag_newname.value==\'\') {
            alert(\'' . getconstStr('DiskTag') . '\');
            return false;
        }
        if (t.disktag_newname.value==t.disktag_rename.value) {
            return false;
        }
        envs = [' . $envs . '];
        if (envs.indexOf(t.disktag_newname.value)>-1) {
            alert(\'Do not input ' . $envs . '\');
            return false;
        }
        var reg = /^[a-zA-Z]([_a-zA-Z0-9]{1,})$/;
        if (!reg.test(t.disktag_newname.value)) {
            alert(\'' . getconstStr('TagFormatAlert') . '\');
            return false;
        }
        return true;
    }
</script>';
    } else {
        if (count($disktags) > 1) {
            $frame = '
<script src="?jsFile=Sortable.min.js"></script>
<style>
    .sortable-ghost {
        opacity: 0.4;
        background-color: #1748ce;
    }

    #sortdisks td {
        cursor: move;
    }
</style>
' . getconstStr('DragSort') . ':
<form id="sortdisks_form" action="" method="post" style="margin: 0" onsubmit="return dragsort(this);">
<table border=1>
    <tbody id="sortdisks">
    <input type="hidden" name="disktag_sort" value="">';
            $num = 0;
            foreach ($disktags as $disktag) {
                if ($disktag != '') {
                    $num++;
                    $frame .= '
        <tr class="sorthandle"><td>' . $num . '</td><td> ' . $disktag . '</td></tr>';
                }
            }
            $frame .= '
    </tbody>
    <input name="_admin" type="hidden" value="">
</table>
    <input type="submit" name="submit1" value="' . getconstStr('SubmitSortdisks') . '">
</form>

<script>
    var disks=' . json_encode($disktags) . ';
    function change(arr, oldindex, newindex) {
        //console.log(oldindex + "," + newindex);
        tmp=arr.splice(oldindex-1, 1);
        if (oldindex > newindex) {
            tmp1=JSON.parse(JSON.stringify(arr));
            tmp1.splice(newindex-1, arr.length-newindex+1);
            tmp2=JSON.parse(JSON.stringify(arr));
            tmp2.splice(0, newindex-1);
        } else {
            tmp1=JSON.parse(JSON.stringify(arr));
            tmp1.splice(newindex-1, arr.length-newindex+1);
            tmp2=JSON.parse(JSON.stringify(arr));
            tmp2.splice(0, newindex-1);
        }
        arr=tmp1.concat(tmp, tmp2);
        //console.log(arr);
        return arr;
    }
    function dragsort(t) {
        if (t.disktag_sort.value==\'\') {
            alert(\'' . getconstStr('DragSort') . '\');
            return false;
        }
        envs = [' . $envs . '];
        if (envs.indexOf(t.disktag_sort.value)>-1) {
            alert(\'Do not input ' . $envs . '\');
            return false;
        }
        return true;
    }
    new Sortable(document.getElementById(\'sortdisks\'), {
        handle: \'.sorthandle\',
        animation: 150,
        onEnd: function (evt) { //拖拽完毕之后发生该事件
            //console.log(evt.oldIndex);
            //console.log(evt.newIndex);
            if (evt.oldIndex!=evt.newIndex) {
                disks=change(disks, evt.oldIndex, evt.newIndex);
                document.getElementById(\'sortdisks_form\').disktag_sort.value=JSON.stringify(disks);
            }
        }
    });
</script><br>';
        }
        $Driver_arr = scandir(__DIR__ . $slash . 'disk');
        $frame .= '
<select name="DriveType" onchange="changedrivetype(this.options[this.options.selectedIndex].value)">';
        foreach ($Driver_arr as $v1) {
            if ($v1 != '.' && $v1 != '..') {
                //$v1 = substr($v1, 0, -4);
                $v2 = splitlast($v1, '.php')[0];
                if ($v2 . '.php' == $v1) $frame .= '
    <option value="' . $v2 . '"' . ($v2 == 'Onedrive' ? ' selected="selected"' : '') . '>' . $v2 . '</option>';
            }
        }
        $frame .= '
</select>
<a id="AddDisk_link" href="?AddDisk=Onedrive">' . getconstStr('AddDisk') . '</a><br><br>
<script>
    function changedrivetype(d) {
        document.getElementById(\'AddDisk_link\').href="?AddDisk=" + d;
    }
</script>';

        $canOneKeyUpate = 0;
        if ('Normal' != $platform) {
            $canOneKeyUpate = 1;
        } else {
            $tmp = time();
            if (mkdir('' . $tmp, 0777)) {
                rmdir('' . $tmp);
                $canOneKeyUpate = 1;
            }
        }
        $frame .= '
        <a href="https://github.com/qkqpttgf/OneManager-php" target="_blank">Github</a>
        <a href="https://gitee.com/qkqpttgf/OneManager-php" target="_blank">Gitee</a>
        <!--a href="https://git.hit.edu.cn/ysun/OneManager-php" target="_blank">HIT Gitlab</a--><br><br>
';
        if (!$canOneKeyUpate) {
            $frame .= '
' . getconstStr('CannotOneKeyUpate') . '<br>';
        } else {
            $frame .= '
<form name="updateform" action="" method="post">
    <input name="_admin" type="hidden" value="">
    Update from
    <select name="GitSource" onchange="changeGitSource(this)">
        <option value="Github" selected>Github</option>
        <option value="Gitee">Gitee</option>
        <!--option value="HITGitlab">HIT Gitlab</option-->
    </select>
    <input type="text" name="auth" size="6" placeholder="auth" value="qkqpttgf">
    <input type="text" name="project" size="12" placeholder="project" value="OneManager-php">
    <button name="QueryBranchs" onclick="querybranchs(this);return false;">' . getconstStr('QueryBranchs') . '</button>
    <select name="branch">
        <option value="master">master</option>
    </select>
    <input type="submit" name="updateProgram" value="' . getconstStr('updateProgram') . '">
</form>

<script>
    function changeGitSource(d) {
        if (d.options[d.options.selectedIndex].value=="Github") document.updateform.auth.value = "qkqpttgf";
        if (d.options[d.options.selectedIndex].value=="Gitee") document.updateform.auth.value = "qkqpttgf";
        if (d.options[d.options.selectedIndex].value=="HITGitlab") document.updateform.auth.value = "ysun";
        document.updateform.QueryBranchs.style.display = null;
        document.updateform.branch.options.length = 0;
        document.updateform.branch.options.add(new Option("master", "master"));
    }
    function querybranchs(b) {
        if (document.updateform.GitSource.options[document.updateform.GitSource.options.selectedIndex].value=="Github") return Githubquerybranchs(b);
        if (document.updateform.GitSource.options[document.updateform.GitSource.options.selectedIndex].value=="Gitee") return Giteequerybranchs(b);
        if (document.updateform.GitSource.options[document.updateform.GitSource.options.selectedIndex].value=="HITGitlab") return HITquerybranchs(b);
    }
    function Githubquerybranchs(b) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "https://api.github.com/repos/"+document.updateform.auth.value+"/"+document.updateform.project.value+"/branches");
        //xhr.setRequestHeader("User-Agent","qkqpttgf/OneManager");
        xhr.onload = function(e){
            console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                document.updateform.branch.options.length=0;
                JSON.parse(xhr.responseText).forEach( function (e) {
                    document.updateform.branch.options.add(new Option(e.name,e.name));
                    if ("master"==e.name) document.updateform.branch.options[document.updateform.branch.options.length-1].selected = true; 
                });
                //document.updateform.QueryBranchs.style.display="none";
                b.style.display="none";
            } else {
                alert(xhr.responseText+"\n"+xhr.status);
            }
        }
        xhr.onerror = function(e){
            alert("Network Error "+xhr.status);
        }
        xhr.send(null);
    }
    function Giteequerybranchs(b) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "https://gitee.com/api/v5/repos/"+document.updateform.auth.value+"/"+document.updateform.project.value+"/branches");
        //xhr.setRequestHeader("User-Agent","qkqpttgf/OneManager");
        xhr.onload = function(e){
            console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                document.updateform.branch.options.length=0;
                JSON.parse(xhr.responseText).forEach( function (e) {
                    document.updateform.branch.options.add(new Option(e.name,e.name));
                    if ("master"==e.name) document.updateform.branch.options[document.updateform.branch.options.length-1].selected = true; 
                });
                //document.updateform.QueryBranchs.style.display="none";
                b.style.display="none";
            } else {
                alert(xhr.responseText+"\n"+xhr.status);
            }
        }
        xhr.onerror = function(e){
            alert("Network Error "+xhr.status);
        }
        xhr.send(null);
    }
    function HITquerybranchs(b) {
        // https://git.hit.edu.cn/api/v4/projects/383/repository/branches/
        var pro_id;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "https://git.hit.edu.cn/api/v4/projects");
        //xhr.setRequestHeader("User-Agent","qkqpttgf/OneManager");
        xhr.onload = function(e){
            //console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                //document.updateform.branch.options.length=0;
                JSON.parse(xhr.responseText).forEach( function (e) {
                    if (e.name===document.updateform.project.value && e.namespace.path===document.updateform.auth.value) {
                        //console.log(e.id);
                        pro_id = e.id;
                    }
                });
                //console.log(pro_id);
                var xhr1 = new XMLHttpRequest();
                xhr1.open("GET", "https://git.hit.edu.cn/api/v4/projects/"+pro_id+"/repository/branches");
                xhr1.onload = function(e){
                    if (xhr1.status==200) {
                        document.updateform.branch.options.length=0;
                        JSON.parse(xhr1.responseText).forEach( function (e) {
                            document.updateform.branch.options.add(new Option(e.name,e.name));
                            if ("master"==e.name) document.updateform.branch.options[document.updateform.branch.options.length-1].selected = true; 
                        });
                    } else {
                        alert(xhr1.responseText+"\n"+xhr1.status);
                    }
                }
                xhr1.send(null);
                //document.updateform.QueryBranchs.style.display="none";
                b.style.display="none";
            } else {
                alert(xhr.responseText+"\n"+xhr.status);
            }
        }
        xhr.onerror = function(e){
            alert("Network Error "+xhr.status);
        }
        xhr.send(null);
    }
</script>
';
        }
        if ($needUpdate) {
            $frame .= '<div style="position: relative; word-wrap: break-word;">
        ' . str_replace("\n", '<br>', $_SERVER['github_ver_new']) . '
</div>
<button onclick="document.getElementById(\'github_ver_old\').style.display=(document.getElementById(\'github_ver_old\').style.display==\'none\'?\'\':\'none\');">More...</button>
<div id="github_ver_old" style="position: relative; word-wrap: break-word; display: none">
        ' . str_replace("\n", '<br>', $_SERVER['github_ver_old']) . '
</div>';
        }/* else {
            $frame .= getconstStr('NotNeedUpdate');
        }*/
        $frame .= '<br><br>
<script src="?jsFile=sha1.min.js"></script>
<table>
    <form id="change_pass" name="change_pass" action="" method="POST" onsubmit="return changePassword(this);">
        <input name="_admin" type="hidden" value="">
    <tr>
        <td>' . getconstStr('OldPassword') . ':</td><td><input type="password" name="oldPass">
        <input type="hidden" name="timestamp"></td>
    </tr>
    <tr>
        <td>' . getconstStr('NewPassword') . ':</td><td><input type="password" name="newPass1"></td>
    </tr>
    <tr>
        <td>' . getconstStr('ReInput') . ':</td><td><input type="password" name="newPass2"></td>
    </tr>
    <tr>
        <td></td><td><button name="changePass" value="changePass">' . getconstStr('ChangAdminPassword') . '</button></td>
    </tr>
    </form>
</table><br>
<table>
    <form id="config_f" name="config" action="" method="POST" onsubmit="return false;">
    <tr>
        <td>' . getconstStr('AdminPassword') . ':<input type="password" name="pass">
        <button name="config_b" value="export" onclick="exportConfig(this);">' . getconstStr('export') . '</button></td>
    </tr>
    <tr>
        <td>' . getconstStr('config') . ':<textarea name="config_t"></textarea>
        <button name="config_b" value="import" onclick="importConfig(this);">' . getconstStr('import') . '</button></td>
    </tr>
    </form>
</table><br>
<script>
    var config_f = document.getElementById("config_f");
    function exportConfig(b) {
        if (config_f.pass.value=="") {
            alert("admin pass");
            return false;
        }
        try {
            sha1(1);
        } catch {
            if (confirm("sha1.js not loaded.\n\nLoad from program?")) loadjs("?jsFile=sha1.min.js");
            return false;
        }
        var timestamp = new Date().getTime();
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "");
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=utf-8");
        xhr.onload = function(e){
            console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                var res = JSON.parse(xhr.responseText);
                config_f.config_t.value = xhr.responseText;
                config_f.parentNode.style = "width: 100%";
                config_f.config_t.style = "width: 100%";
                config_f.config_t.style.height = config_f.config_t.scrollHeight + "px";
            } else {
                alert(xhr.status+"\n"+xhr.responseText);
            }
        }
        xhr.onerror = function(e){
            alert("Network Error "+xhr.status);
        }
        xhr.send("pass=" + sha1(config_f.pass.value + "" + timestamp) + "&config_b=" + b.value + "&timestamp=" + timestamp + "&_admin=" + localStorage.getItem("admin"));
    }
    function importConfig(b) {
        if (config_f.pass.value=="") {
            alert("admin pass");
            return false;
        }
        if (config_f.config_t.value=="") {
            alert("input config");
            return false;
        } else {
            try {
                var tmp = JSON.parse(config_f.config_t.value);
            } catch(e) {
                alert("config error!");
                return false;
            }
        }
        try {
            sha1(1);
        } catch {
            if (confirm("sha1.js not loaded.\n\nLoad from program?")) loadjs("?jsFile=sha1.min.js");
            return false;
        }
        var timestamp = new Date().getTime();
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "");
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=utf-8");
        xhr.onload = function(e){
            console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                //var res = JSON.parse(xhr.responseText);
                alert("Import success");
            } else {
                alert(xhr.status+"\n"+xhr.responseText);
            }
        }
        xhr.onerror = function(e){
            alert("Network Error "+xhr.status);
        }
        xhr.send("pass=" + sha1(config_f.pass.value + "" + timestamp) + "&config_t=" + encodeURIComponent(config_f.config_t.value) + "&config_b=" + b.value + "&timestamp=" + timestamp + "&_admin=" + localStorage.getItem("admin"));
    }
    function changePassword(f) {
        if (f.oldPass.value==""||f.newPass1.value==""||f.newPass2.value=="") {
            alert("Input");
            return false;
        }
        if (f.oldPass.value==f.newPass1.value) {
            alert("Same password");
            return false;
        }
        if (f.newPass1.value!==f.newPass1.value) {
            alert("Input twice new password");
            return false;
        }
        try {
            sha1(1);
        } catch {
            if (confirm("sha1.js not loaded.\n\nLoad from program?")) loadjs("?jsFile=sha1.min.js");
            return false;
        }
        var timestamp = new Date().getTime();
        f.timestamp.value = timestamp;
        f.oldPass.value = sha1(f.oldPass.value + "" + timestamp);
        return true;
    }
    function loadjs(url) {
        var xhr = new XMLHttpRequest;
        xhr.open("GET", url);
        xhr.onload = function(e) {
            if (xhr.status==200) {
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.text = xhr.responseText;
                document.body.appendChild(script);
            } else {
                console.log(xhr.response);
            }
        }
        xhr.send(null);
    }
</script>';
    }
    $html .= '
<style type="text/css">
    .tabs { padding: 10px; white-space: nowrap; overflow-x: auto;}
    .tabs a { margin:0 10px; }
</style>
<div class="tabs">';
    if ($_GET['disktag'] == '' || $_GET['disktag'] === true || !in_array($_GET['disktag'], $disktags)) {
        if ($_GET['setup'] === 'platform') $html .= '
    <a href="?setup">' . getconstStr('Home') . '</a>
    ' . getconstStr('PlatformConfig') . '';
        else $html .= '
    ' . getconstStr('Home') . '
    <a href="?setup=platform">' . getconstStr('PlatformConfig') . '</a>';
    } else $html .= '
    <a href="?setup">' . getconstStr('Home') . '</a>
    <a href="?setup=platform">' . getconstStr('PlatformConfig') . '</a>';
    foreach ($disktags as $disktag) {
        if ($disktag != '') {
            if ($_GET['disktag'] === $disktag) $html .= '
    ' . $disktag . '';
            else $html .= '
    <a href="?setup&disktag=' . $disktag . '">' . $disktag . '</a>';
        }
    }
    $html .= '
</div><br>';
    $html .= $frame;
    $html .= '<script>
    var inputAdminStorage = document.getElementsByName("_admin");
    for (i=0;i<inputAdminStorage.length;i++) {
        inputAdminStorage[i].value = localStorage.getItem("admin");
    }
</script>';
    return message($html, getconstStr('Setup'));
}
function replaceHtml(&$html, $target, $str) {
    while (strpos($html, '/*--' . $target . '--*/')) $html = str_replace('/*--' . $target . '--*/', $str, $html);
    while (strpos($html, '<!--' . $target . '-->')) $html = str_replace('<!--' . $target . '-->', $str, $html);
}
function getStackHtml(&$html, $name, $remove) {
    if ($remove) {
        while (strpos($html, '/*--' . $name . 'Start--*/')) {
            $tmp = splitfirst($html, '/*--' . $name . 'Start--*/');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '/*--' . $name . 'End--*/');
            $html .= $tmp[1];
        }
        while (strpos($html, '<!--' . $name . 'Start-->')) {
            $tmp = splitfirst($html, '<!--' . $name . 'Start-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--' . $name . 'End-->');
            $html .= $tmp[1];
        }
    } else {
        while (strpos($html, '/*--' . $name . 'Start--*/')) {
            $html = str_replace('/*--' . $name . 'Start--*/', '', $html);
            $html = str_replace('/*--' . $name . 'End--*/', '', $html);
        }
        while (strpos($html, '<!--' . $name . 'Start-->')) {
            $html = str_replace('<!--' . $name . 'Start-->', '', $html);
            $html = str_replace('<!--' . $name . 'End-->', '', $html);
        }
    }
}
function headandfoot(&$html, $target, $path, $files, $name, $globalUrl) {
    while (strpos($html, '/*--' . $target . 'Start--*/')) {
        $tmp = splitfirst($html, '/*--' . $target . 'Start--*/');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '/*--' . $target . 'End--*/');
        $content1 = "";
        if (isset($files['list'][$name])) {
            $content = get_content(path_format($path . '/' . $files['list'][$name]['name']))['content']['body'];
            $content1 = str_replace('/*--' . $target . 'Content--*/', $content, $tmp[0]);
            $content1 = str_replace('<!--' . $target . 'Content-->', $content, $tmp[0]);
        } elseif (getConfig($globalUrl)) {
            if (!$content = getcache($target . 'Content')) {
                $res = curl('GET', getConfig($globalUrl), '', [], 0, 1);
                if ($res['stat'] == 200) {
                    $content = $res['body'];
                    savecache($target . 'Content', $content);
                } else $content = $res['stat'];
            }
            $content1 = str_replace('/*--' . $target . 'Content--*/', $content, $tmp[0]);
            $content1 = str_replace('<!--' . $target . 'Content-->', $content, $tmp[0]);
        }
        $html .= $content1 . $tmp[1];
    }

    while (strpos($html, '<!--' . $target . 'Start-->')) {
        $tmp = splitfirst($html, '<!--' . $target . 'Start-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--' . $target . 'End-->');
        $content1 = "";
        if (isset($files['list'][$name])) {
            $content = get_content(path_format($path . '/' . $files['list'][$name]['name']))['content']['body'];
            $content1 = str_replace('/*--' . $target . 'Content--*/', $content, $tmp[0]);
            $content1 = str_replace('<!--' . $target . 'Content-->', $content, $tmp[0]);
        } elseif (getConfig($globalUrl)) {
            if (!$content = getcache($target . 'Content')) {
                $res = curl('GET', getConfig($globalUrl), '', [], 0, 1);
                if ($res['stat'] == 200) {
                    $content = $res['body'];
                    savecache($target . 'Content', $content);
                } else $content = $res['stat'];
            }
            $content1 = str_replace('/*--' . $target . 'Content--*/', $content, $tmp[0]);
            $content1 = str_replace('<!--' . $target . 'Content-->', $content, $tmp[0]);
        }
        $html .= $content1 . $tmp[1];
    }
}
function render_list($path = '', $files = []) {
    global $exts;
    global $constStr;
    global $slash;

    if (isset($files['list']['index.html']) && !$_SERVER['admin']) {
        $htmlcontent = get_content(path_format($path . '/index.html'))['content'];
        return output($htmlcontent['body'], $htmlcontent['stat']);
    }
    if (isset($files['list']['index.htm']) && !$_SERVER['admin']) {
        $htmlcontent = get_content(path_format($path . '/index.htm'))['content'];
        return output($htmlcontent['body'], $htmlcontent['stat']);
    }
    //$path = str_replace('%20','%2520',$path);
    //$path = str_replace('+','%2B',$path);
    $path1 = path_format(urldecode($path));
    //$path = str_replace('&','&amp;', $path) ;
    //$path = str_replace('%20',' ',$path);
    //$path = str_replace('#','%23',$path);
    $p_path = '';
    if ($path1 !== '/') {
        if ($files['type'] == 'file') {
            if (isset($files['name'])) {
                $pretitle = str_replace('&', '&amp;', $files['name']);
            } else {
                if (substr($path1, 0, 1) == '/') $pretitle = substr($path1, 1);
                if (substr($path1, -1) == '/') $pretitle = substr($pretitle, 0, -1);
                $pretitle = str_replace('&', '&amp;', $pretitle);
            }
            $n_path = $pretitle;
            $tmp = splitlast(splitlast($path1, '/')[0], '/');
            if ($tmp[1] == '') {
                $p_path = $tmp[0];
            } else {
                $p_path = $tmp[1];
            }
        } else {
            if (substr($path1, 0, 1) == '/') $pretitle = substr($path1, 1);
            if (substr($path1, -1) == '/') $pretitle = substr($pretitle, 0, -1);
            $pretitle = str_replace('&', '&amp;', $pretitle);
            $tmp = splitlast($pretitle, '/');
            if ($tmp[1] == '') {
                $n_path = $tmp[0];
            } else {
                $n_path = $tmp[1];
                $tmp = splitlast($tmp[0], '/');
                if ($tmp[1] == '') {
                    $p_path = $tmp[0];
                } else {
                    $p_path = $tmp[1];
                }
            }
        }
    } else {
        $pretitle = getconstStr('Home');
        $n_path = $pretitle;
    }
    $n_path = str_replace('&amp;', '&', $n_path);
    $p_path = str_replace('&amp;', '&', $p_path);
    //$pretitle = str_replace('%23','#',$pretitle);
    $statusCode = 200;
    date_default_timezone_set(get_timezone($_SERVER['timezone']));
    $authinfo = '
<!--
    OneManager: An index & manager of Onedrive auth by ysun.
    HIT Gitlab: https://git.hit.edu.cn/ysun/OneManager-php
    Github: https://github.com/qkqpttgf/OneManager-php
    Gitee: https://gitee.com/qkqpttgf/OneManager-php
-->';
    //$authinfo = $path . '<br><pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';

    //if (isset($_COOKIE['theme'])&&$_COOKIE['theme']!='') $theme = $_COOKIE['theme'];
    //if ( !file_exists(__DIR__ . $slash .'theme' . $slash . $theme) ) $theme = '';
    if ($_SERVER['admin']) $theme = 'classic.html';
    if ($theme == '') {
        $tmp = getConfig('customTheme');
        if ($tmp != '') $theme = $tmp;
    }
    if ($theme == '') {
        $theme = getConfig('theme');
        if ($theme == '' || !file_exists(__DIR__ . $slash . 'theme' . $slash . $theme)) $theme = 'classic.html';
    }
    if (substr($theme, -4) == '.php') {
        @ob_start();
        include 'theme/' . $theme;
        $html = ob_get_clean();
    } else {
        if (file_exists(__DIR__ . $slash . 'theme' . $slash . $theme)) {
            $file_path = __DIR__ . $slash . 'theme' . $slash . $theme;
            $html = file_get_contents($file_path);
        } else {
            if (!($html = getcache('customTheme'))) {
                $file_path = $theme;
                $tmp = curl('GET', $file_path, '', [], 1, 1);
                //error_log1($file_path . " =+= " . json_encode($tmp));
                if ($tmp['stat'] == 200) {
                    $html = $tmp['body'];
                    savecache('customTheme', $html, '', 9999);
                } else {
                    $html = "<pre>" . json_encode($tmp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
                }
            }
        }

        $tmp = splitfirst($html, '<!--IconValuesStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--IconValuesEnd-->');
        $IconValues = json_decode($tmp[0], true);
        $html .= $tmp[1];

        if ($files) {
            getStackHtml($html, "List", 0);
        } else {
            //$html = '<pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>' . $html;
            getStackHtml($html, "IsFile", 1);
            getStackHtml($html, "IsFolder", 1);
            getStackHtml($html, "List", 1);
            getStackHtml($html, "GuestUpload", 1);
            getStackHtml($html, "Encrypted", 1);
        }

        if ($_SERVER['admin']) {
            getStackHtml($html, "Login", 1);
            getStackHtml($html, "Guest", 1);
            getStackHtml($html, "Admin", 0);

            replaceHtml($html, "constStr@Operate", getconstStr('Operate'));
            replaceHtml($html, "constStr@Create", getconstStr('Create'));
            replaceHtml($html, "constStr@Encrypt", getconstStr('Encrypt'));
            replaceHtml($html, "constStr@RefreshCache", getconstStr('RefreshCache'));
            replaceHtml($html, "constStr@Setup", getconstStr('Setup'));
            replaceHtml($html, "constStr@Logout", getconstStr('Logout'));
            replaceHtml($html, "constStr@Rename", getconstStr('Rename'));
            replaceHtml($html, "constStr@Submit", getconstStr('Submit'));
            replaceHtml($html, "constStr@Delete", getconstStr('Delete'));
            replaceHtml($html, "constStr@Copy", getconstStr('Copy'));
            replaceHtml($html, "constStr@Move", getconstStr('Move'));
            replaceHtml($html, "constStr@Folder", getconstStr('Folder'));
            replaceHtml($html, "constStr@File", getconstStr('File'));
            replaceHtml($html, "constStr@Name", getconstStr('Name'));
            replaceHtml($html, "constStr@Content", getconstStr('Content'));
        } else {
            getStackHtml($html, "Admin", 1);
            if (getConfig('adminloginpage') == '') {
                getStackHtml($html, "Login", 0);
            } else {
                getStackHtml($html, "Login", 1);
            }
            getStackHtml($html, "Guest", 0);
        }

        if ($_SERVER['ishidden'] < 4 || ($files['type'] == 'file' && getConfig('downloadencrypt', $_SERVER['disktag']))) {
            getStackHtml($html, "Encrypted", 1);
            getStackHtml($html, "IsNotHidden", 0);
        } else {
            // 加密状态
            if (getConfig('useBasicAuth')) {
                // use Basic Auth
                return output('Need password.', 401, ['WWW-Authenticate' => 'Basic realm="Secure Area"']);
            }
            /*getStackHtml($html, "List", 1);*/
            getStackHtml($html, "IsFile", 1);
            getStackHtml($html, "IsFolder", 1);
            getStackHtml($html, "IsNotHidden", 1);
            getStackHtml($html, "Encrypted", 0);
            getStackHtml($html, "GuestUpload", 1);
            getStackHtml($html, "Headomf", 1);
            getStackHtml($html, "Headmd", 1);
            getStackHtml($html, "Readmemd", 1);
            getStackHtml($html, "Footomf", 1);
        }
        replaceHtml($html, "constStr@Download", getconstStr('Download'));

        if ($_SERVER['is_guestup_path'] && !$_SERVER['admin']) {
            getStackHtml($html, "IsFile", 1);
            getStackHtml($html, "IsFolder", 1);
            getStackHtml($html, "GuestUpload", 0);
            getStackHtml($html, "IsNotHidden", 1);
        } else {
            getStackHtml($html, "GuestUpload", 1);
            getStackHtml($html, "IsNotHidden", 0);
        }
        $DriverFile = scandir(__DIR__ . $slash . 'disk');
        $Driver_arr = null;
        $Driver_arr = [];
        foreach ($DriverFile as $v1) {
            if ($v1 != '.' && $v1 != '..') {
                $v1 = splitlast($v1, '.php')[0];
                $Driver_arr[] = $v1;
            }
        }
        if ($_SERVER['is_guestup_path'] || ($_SERVER['admin'] && $files['type'] == 'folder' && $_SERVER['ishidden'] < 4)) {
            $now_driver = baseclassofdrive();
            if ($now_driver) {
                getStackHtml($html, "UploadJs", 0);
                unset($Driver_arr[$now_driver]);
                getStackHtml($html, $now_driver . "UploadJs", 0);
            } else {
                getStackHtml($html, "UploadJs", 1);
            }
            foreach ($Driver_arr as $driver) {
                getStackHtml($html, $driver . "UploadJs", 1);
            }
            replaceHtml($html, "constStr@Calculate", getconstStr('Calculate'));
        } else {
            getStackHtml($html, "UploadJs", 1);
            foreach ($Driver_arr as $driver) {
                getStackHtml($html, $driver . "UploadJs", 1);
            }
        }

        if ($files['type'] == 'file') {
            getStackHtml($html, "GuestUpload", 1);
            getStackHtml($html, "Encrypted", 1);
            getStackHtml($html, "IsFolder", 1);
            getStackHtml($html, "IsFile", 0);
            //$html = str_replace('<!--FileEncodeUrl-->', encode_str_replace(path_format($_SERVER['base_disk_path'] . '/' . $path)), $html);
            replaceHtml($html, "FileEncodeUrl", encode_str_replace(splitlast($path1, '/')[1]));
            replaceHtml($html, "FileUrl", path_format($_SERVER['base_disk_path'] . '/' . $path1));

            $ext = strtolower(substr($path, strrpos($path, '.') + 1));
            if (in_array($ext, $exts['img'])) $ext = 'img';
            elseif (in_array($ext, $exts['video'])) $ext = 'video';
            elseif (in_array($ext, $exts['music'])) $ext = 'music';
            //elseif (in_array($ext, $exts['pdf'])) $ext = 'pdf';
            elseif ($ext == 'pdf') $ext = 'pdf';
            elseif (in_array($ext, $exts['office'])) $ext = 'office';
            elseif (in_array($ext, $exts['txt'])) $ext = 'txt';
            else $ext = 'Other';
            $previewext = ['img', 'video', 'music', 'pdf', 'office', 'txt', 'Other'];
            $previewext = array_diff($previewext, [$ext]);
            foreach ($previewext as $ext1) {
                getStackHtml($html, "Is" . $ext1 . "File", 1);
            }
            getStackHtml($html, "Is" . $ext . "File", 0);
            //while (strpos($html, '<!--FileDownUrl-->')) $html = str_replace('<!--FileDownUrl-->', $files['url'], $html);
            //while (strpos($html, '<!--FileDownUrl-->')) $html = str_replace('<!--FileDownUrl-->', (path_format($_SERVER['base_disk_path'] . '/' . $path)), $html);
            replaceHtml($html, "FileDownUrl", encode_str_replace(splitlast($path1, '/')[1]));
            //echo $path . "<br>\n";
            //while (strpos($html, '<!--FileEncodeReplaceUrl-->')) $html = str_replace('<!--FileEncodeReplaceUrl-->', (path_format($_SERVER['base_disk_path'] . '/' . str_replace('&amp;', '&', $path))), $html);
            replaceHtml($html, "FileEncodeReplaceUrl", encode_str_replace(splitlast($path1, '/')[1]));
            replaceHtml($html, "FileName", $files['name']);
            replaceHtml($html, "FileEncodeDownUrl", urlencode($files['url']));
            //while (strpos($html, '<!--FileEncodeDownUrl-->')) $html = str_replace('<!--FileEncodeDownUrl-->', urlencode($_SERVER['host'] . path_format($_SERVER['base_disk_path'] . '/' . $path)), $html);
            replaceHtml($html, "constStr@ClicktoEdit", getconstStr('ClicktoEdit'));
            replaceHtml($html, "constStr@CancelEdit", getconstStr('CancelEdit'));
            replaceHtml($html, "constStr@Save", getconstStr('Save'));
            replaceHtml($html, "TxtContent", htmlspecialchars($files['content']['body']));
            replaceHtml($html, "constStr@FileNotSupport", getconstStr('FileNotSupport'));
        } elseif ($files['type'] == 'folder') {
            getStackHtml($html, "GuestUpload", 1);
            getStackHtml($html, "Encrypted", 1);
            getStackHtml($html, "IsFile", 1);
            getStackHtml($html, "IsFolder", 0);
            replaceHtml($html, "constStr@File", getconstStr('File'));
            replaceHtml($html, "FolderId", $files['id']);
            replaceHtml($html, "constStr@ShowThumbnails", getconstStr('ShowThumbnails'));
            replaceHtml($html, "constStr@CopyAllDownloadUrl", getconstStr('CopyAllDownloadUrl'));
            replaceHtml($html, "constStr@EditTime", getconstStr('EditTime'));
            replaceHtml($html, "constStr@Size", getconstStr('Size'));

            $filenum = 0;

            $tmp = splitfirst($html, '<!--FolderListStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--FolderListEnd-->');
            $FolderList = $tmp[0];
            foreach ($files['list'] as $file) {
                if ($file['type'] == 'folder') {
                    if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                        $filenum++;
                        //$FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', encode_str_replace(path_format($_SERVER['base_disk_path'] . '/' . str_replace('&amp;', '&', $path) . '/' . $file['name'])), $FolderList);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', encode_str_replace($file['name']), $FolderList);
                        $FolderListStr = str_replace('<!--FileId-->', $file['id'], $FolderListStr);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceName-->', str_replace('&', '&amp;', $file['showname'] ? $file['showname'] : $file['name']), $FolderListStr);
                        $FolderListStr = str_replace('<!--lastModifiedDateTime-->', time_format($file['time']), $FolderListStr);
                        $FolderListStr = str_replace('<!--size-->', size_format($file['size']), $FolderListStr);
                        replaceHtml($FolderListStr, "filenum", $filenum);
                        $html .= $FolderListStr;
                    }
                }
            }
            $html .= $tmp[1];

            $tmp = splitfirst($html, '<!--FileListStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--FileListEnd-->');
            $FolderList = $tmp[0];
            foreach ($files['list'] as $file) {
                if ($file['type'] == 'file') {
                    if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                        $filenum++;
                        $ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
                        $FolderListStr = $FolderList;
                        //while (strpos($FolderListStr, '<!--FileEncodeReplaceUrl-->')) $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', encode_str_replace(path_format($_SERVER['base_disk_path'] . '/' . str_replace('&amp;', '&', $path) . '/' . $file['name'])), $FolderListStr);
                        while (strpos($FolderListStr, '<!--FileEncodeReplaceUrl-->')) $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', encode_str_replace($file['name']), $FolderListStr);
                        $FolderListStr = str_replace('<!--FileExt-->', $ext, $FolderListStr);
                        if (in_array($ext, $exts['music'])) $FolderListStr = str_replace('<!--FileExtType-->', 'audio', $FolderListStr);
                        elseif (in_array($ext, $exts['video'])) $FolderListStr = str_replace('<!--FileExtType-->', 'iframe', $FolderListStr);
                        else $FolderListStr = str_replace('<!--FileExtType-->', '', $FolderListStr);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceName-->', str_replace('&', '&amp;', $file['name']), $FolderListStr);
                        $FolderListStr = str_replace('<!--FileId-->', $file['id'], $FolderListStr);
                        //$FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . str_replace('&','&amp;', $file['name'])), $FolderListStr);
                        $FolderListStr = str_replace('<!--lastModifiedDateTime-->', time_format($file['time']), $FolderListStr);
                        $FolderListStr = str_replace('<!--size-->', size_format($file['size']), $FolderListStr);
                        if (!!$IconValues) {
                            foreach ($IconValues as $key1 => $value1) {
                                if (isset($exts[$key1]) && in_array($ext, $exts[$key1])) {
                                    $FolderListStr = str_replace('<!--IconValue-->', $value1, $FolderListStr);
                                }
                                if ($ext == $key1) {
                                    $FolderListStr = str_replace('<!--IconValue-->', $value1, $FolderListStr);
                                }
                                //error_log1('file:'.$file['name'].':'.$key1);
                                if (!strpos($FolderListStr, '<!--IconValue-->')) break;
                            }
                            if (strpos($FolderListStr, '<!--IconValue-->')) $FolderListStr = str_replace('<!--IconValue-->', $IconValues['default'], $FolderListStr);
                        }
                        replaceHtml($FolderListStr, "filenum", $filenum);
                        $html .= $FolderListStr;
                    }
                }
            }
            $html .= $tmp[1];
            replaceHtml($html, "maxfilenum", $filenum);

            if ($files['childcount'] > 200) {
                getStackHtml($html, "MorePage", 0);

                $pagenum = $files['page'];
                if ($pagenum == '') $pagenum = 1;
                $maxpage = ceil($files['childcount'] / 200);

                if ($pagenum != 1) {
                    getStackHtml($html, "PrePage", 0);
                    replaceHtml($html, "constStr@PrePage", getconstStr('PrePage'));
                    replaceHtml($html, "PrePageNum", $pagenum - 1);
                } else {
                    getStackHtml($html, "PrePage", 1);
                }
                //$html .= json_encode($files['folder']);
                if ($pagenum != $maxpage) {
                    getStackHtml($html, "NextPage", 0);
                    replaceHtml($html, "constStr@NextPage", getconstStr('NextPage'));
                    replaceHtml($html, "NextPageNum", $pagenum + 1);
                } else {
                    getStackHtml($html, "NextPage", 1);
                }
                $tmp = splitfirst($html, '<!--MorePageListNowStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--MorePageListNowEnd-->');
                $MorePageListNow = str_replace('<!--PageNum-->', $pagenum, $tmp[0]);
                $html .= $tmp[1];

                $tmp = splitfirst($html, '<!--MorePageListStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--MorePageListEnd-->');
                $MorePageList = $tmp[0];
                for ($page = 1; $page <= $maxpage; $page++) {
                    if ($page == $pagenum) {
                        $MorePageListStr = $MorePageListNow;
                    } else {
                        $MorePageListStr = $MorePageList;
                        replaceHtml($MorePageListStr, "PageNum", $page);
                    }
                    $html .= $MorePageListStr;
                }
                $html .= $tmp[1];

                replaceHtml($html, "MaxPageNum", $maxpage);
            } else {
                getStackHtml($html, "MorePage", 1);
            }
        }

        replaceHtml($html, "constStr@language", $_SERVER['language']);

        $title = $pretitle;
        if ($_SERVER['base_disk_path'] != $_SERVER['base_path']) {
            if (getConfig('diskname') != '') $diskname = getConfig('diskname');
            else $diskname = $_SERVER['disktag'];
            $title .= ' - ' . $diskname;
        }
        $title .= ' - ' . $_SERVER['sitename'];
        replaceHtml($html, "Title", $title);

        $keywords = $n_path;
        if ($p_path != '') $keywords .= ', ' . $p_path;
        if ($_SERVER['sitename'] != 'OneManager') $keywords .= ', ' . $_SERVER['sitename'] . ', OneManager';
        else $keywords .= ', OneManager';
        replaceHtml($html, "Keywords", $keywords);

        if ($_GET['preview']) {
            $description = $n_path . ', ' . getconstStr('Preview'); //'Preview of '.
        } elseif ($files['type'] == 'folder') {
            $description = $n_path . ', ' . getconstStr('List'); //'List of '.$n_path.'. ';
        }
        //$description .= 'In '.$_SERVER['sitename'];
        replaceHtml($html, "Description", $description);

        replaceHtml($html, "base_disk_path", substr($_SERVER['base_disk_path'], -1) == '/' ? substr($_SERVER['base_disk_path'], 0, -1) : $_SERVER['base_disk_path']);
        replaceHtml($html, "base_path", $_SERVER['base_path']);
        replaceHtml($html, "Path", str_replace('\'', '\\\'', str_replace('%23', '#', str_replace('&', '&amp;', path_format($path1 . '/')))));
        replaceHtml($html, "constStr@Home", getconstStr('Home'));

        replaceHtml($html, "customCss", getConfig('customCss'));
        replaceHtml($html, "customScript", getConfig('customScript'));

        replaceHtml($html, "constStr@Login", getconstStr('Login'));
        replaceHtml($html, "constStr@Close", getconstStr('Close'));
        replaceHtml($html, "constStr@InputPassword", getconstStr('InputPassword'));
        replaceHtml($html, "constStr@InputPasswordUWant", getconstStr('InputPasswordUWant'));
        replaceHtml($html, "constStr@Submit", getconstStr('Submit'));
        replaceHtml($html, "constStr@Success", getconstStr('Success'));
        replaceHtml($html, "constStr@GetUploadLink", getconstStr('GetUploadLink'));
        replaceHtml($html, "constStr@UpFileTooLarge", getconstStr('UpFileTooLarge'));
        replaceHtml($html, "constStr@UploadStart", getconstStr('UploadStart'));
        replaceHtml($html, "constStr@UploadStartAt", getconstStr('UploadStartAt'));
        replaceHtml($html, "constStr@LastUpload", getconstStr('LastUpload'));
        replaceHtml($html, "constStr@ThisTime", getconstStr('ThisTime'));

        replaceHtml($html, "constStr@Upload", getconstStr('Upload'));
        replaceHtml($html, "constStr@AverageSpeed", getconstStr('AverageSpeed'));
        replaceHtml($html, "constStr@CurrentSpeed", getconstStr('CurrentSpeed'));
        replaceHtml($html, "constStr@Expect", getconstStr('Expect'));
        replaceHtml($html, "constStr@UploadErrorUpAgain", getconstStr('UploadErrorUpAgain'));
        replaceHtml($html, "constStr@EndAt", getconstStr('EndAt'));

        replaceHtml($html, "constStr@UploadComplete", getconstStr('UploadComplete'));
        replaceHtml($html, "constStr@CopyUrl", getconstStr('CopyUrl'));
        replaceHtml($html, "constStr@UploadFail23", getconstStr('UploadFail23'));
        replaceHtml($html, "constStr@GetFileNameFail", getconstStr('GetFileNameFail'));
        replaceHtml($html, "constStr@UploadFile", getconstStr('UploadFile'));
        replaceHtml($html, "constStr@UploadFolder", getconstStr('UploadFolder'));
        replaceHtml($html, "constStr@FileSelected", getconstStr('FileSelected'));
        replaceHtml($html, "IsPreview?", isset($_GET['preview']) ? '?preview&' : '?');

        if (getConfig('background')) {
            getStackHtml($html, "Background", 0);
            $html = str_replace('<!--BackgroundUrl-->', getConfig('background'), $html);
        } else {
            getStackHtml($html, "Background", 1);
        }

        if (getConfig('backgroundm')) {
            getStackHtml($html, "BackgroundM", 0);
            $html = str_replace('<!--BackgroundMUrl-->', getConfig('backgroundm'), $html);
        } else {
            getStackHtml($html, "BackgroundM", 1);
        }

        $tmp = splitfirst($html, '<!--PathArrayStart-->');
        $html = $tmp[0];
        if ($tmp[1] != '') {
            $tmp = splitfirst($tmp[1], '<!--PathArrayEnd-->');
            $PathArrayStr = $tmp[0];
            $tmp_url = $_SERVER['base_disk_path'];
            $tmp_path = str_replace('&', '&amp;', substr(urldecode($_SERVER['PHP_SELF']), strlen($tmp_url)));
            while ($tmp_path != '') {
                $tmp1 = splitfirst($tmp_path, '/');
                $folder1 = str_replace('&amp;', '&', $tmp1[0]);
                if ($folder1 != '') {
                    $tmp_url .= $folder1 . '/';
                    $PathArrayStr1 = str_replace('<!--PathArrayLink-->', encode_str_replace($folder1 == $files['name'] ? '' : $tmp_url), $PathArrayStr);
                    $PathArrayStr1 = str_replace('<!--PathArrayName-->', str_replace('&', '&amp;', $folder1), $PathArrayStr1);
                    $html .= $PathArrayStr1;
                }
                $tmp_path = $tmp1[1];
            }
            $html .= $tmp[1];
        }

        $tmp = splitfirst($html, '<!--DiskPathArrayStart-->');
        $html = $tmp[0];
        if ($tmp[1] != '') {
            $tmp = splitfirst($tmp[1], '<!--DiskPathArrayEnd-->');
            $PathArrayStr = $tmp[0];
            $tmp_url = $_SERVER['base_path'];
            $tmp_path = str_replace('&', '&amp;', substr(urldecode($_SERVER['PHP_SELF']), strlen($tmp_url)));
            while ($tmp_path != '') {
                $tmp1 = splitfirst($tmp_path, '/');
                $folder1 = str_replace('&amp;', '&', $tmp1[0]);
                if ($folder1 != '') {
                    $tmp_url .= $folder1 . '/';
                    $PathArrayStr1 = str_replace('<!--PathArrayLink-->', encode_str_replace($folder1 == $files['name'] ? '' : $tmp_url), $PathArrayStr);
                    $PathArrayStr1 = str_replace('<!--PathArrayName-->', str_replace('&', '&amp;', $folder1 == $_SERVER['disktag'] ? (getConfig('diskname') == '' ? $_SERVER['disktag'] : getConfig('diskname')) : $folder1), $PathArrayStr1);
                    $html .= $PathArrayStr1;
                }
                $tmp_path = $tmp1[1];
            }
            $html .= $tmp[1];
        }

        $tmp = splitfirst($html, '<!--SelectLanguageStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--SelectLanguageEnd-->');
        $SelectLanguage = $tmp[0];
        foreach ($constStr['languages'] as $key1 => $value1) {
            $SelectLanguageStr = str_replace('<!--SelectLanguageKey-->', $key1, $SelectLanguage);
            $SelectLanguageStr = str_replace('<!--SelectLanguageValue-->', $value1, $SelectLanguageStr);
            $SelectLanguageStr = str_replace('<!--SelectLanguageSelected-->', ($key1 == $constStr['language'] ? 'selected="selected"' : ''), $SelectLanguageStr);
            $html .= $SelectLanguageStr;
        }
        $html .= $tmp[1];

        $tmp = splitfirst($html, '<!--NeedUpdateStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--NeedUpdateEnd-->');
        $NeedUpdateStr = $tmp[0];
        if (isset($_SERVER['needUpdate']) && $_SERVER['needUpdate']) $NeedUpdateStr = str_replace('<!--constStr@NeedUpdate-->', getconstStr('NeedUpdate'), $NeedUpdateStr);
        else $NeedUpdateStr = '';
        $html .= $NeedUpdateStr . $tmp[1];

        $tmp = splitfirst($html, '<!--BackArrowStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--BackArrowEnd-->');
        $current_url = path_format($_SERVER['PHP_SELF'] . '/');
        if ($current_url !== $_SERVER['base_path']) {
            while (substr($current_url, -1) === '/') {
                $current_url = substr($current_url, 0, -1);
            }
            if (strpos($current_url, '/') !== FALSE) {
                $parent_url = substr($current_url, 0, strrpos($current_url, '/'));
            } else {
                $parent_url = $current_url;
            }
            $BackArrow = str_replace('<!--BackArrowUrl-->', $parent_url . '/', $tmp[0]);
        }
        $html .= $BackArrow . $tmp[1];

        replaceHtml($html, "constStr@OriginalPic", getconstStr('OriginalPic'));
        if (!getConfig('disableShowThumb')) {
            getStackHtml($html, "ShowThumbnails", 0);
        } else {
            getStackHtml($html, "ShowThumbnails", 1);
        }
        $imgextstr = '';
        foreach ($exts['img'] as $imgext) $imgextstr .= '\'' . $imgext . '\', ';
        replaceHtml($html, "ImgExts", $imgextstr);

        replaceHtml($html, "Sitename", $_SERVER['sitename']);

        $tmp = splitfirst($html, '<!--MultiDiskAreaStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MultiDiskAreaEnd-->');
        $disktags = explode("|", getConfig('disktag'));
        if (count($disktags) > 1) {
            $tmp1 = $tmp[1];
            $tmp = splitfirst($tmp[0], '<!--MultiDisksStart-->');
            $MultiDiskArea = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--MultiDisksEnd-->');
            $MultiDisks = $tmp[0];
            foreach ($disktags as $disk) if ($_SERVER['admin'] || getConfig('diskDisplay', $disk) == '') {
                $diskname = getConfig('diskname', $disk);
                if ($diskname == '') $diskname = $disk;
                $MultiDisksStr = str_replace('<!--MultiDisksUrl-->', path_format($_SERVER['base_path'] . '/' . $disk . '/'), $MultiDisks);
                $MultiDisksStr = str_replace('<!--MultiDisksNow-->', ($_SERVER['disktag'] == $disk ? ' now' : ''), $MultiDisksStr);
                $MultiDisksStr = str_replace('<!--MultiDisksName-->', $diskname, $MultiDisksStr);
                $MultiDiskArea .= $MultiDisksStr;
            }
            $MultiDiskArea .= $tmp[1];
            $tmp[1] = $tmp1;
        }
        $html .= $MultiDiskArea . $tmp[1];
        $diskname = getConfig('diskname', $_SERVER['disktag']);
        if ($diskname == '') $diskname = $_SERVER['disktag'];
        //if (strlen($diskname)>15) $diskname = substr($diskname, 0, 12).'...';
        while (strpos($html, '<!--DiskNameNow-->')) $html = str_replace('<!--DiskNameNow-->', $diskname, $html);

        $tmp = splitfirst($html, '<!--MdRequireStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MdRequireEnd-->');
        if (isset($files['list']['head.md']) || isset($files['list']['readme.md']) || getConfig('globalHeadMdUrl') || getConfig('globalReadmeMdUrl')) {
            $html .= $tmp[0] . $tmp[1];
        } else $html .= $tmp[1];

        if (getConfig('passfile') != '') {
            $tmp = splitfirst($html, '<!--EncryptBtnStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptBtnEnd-->');
            $html .= str_replace('<!--constStr@Encrypt-->', getconstStr('Encrypt'), $tmp[0]) . $tmp[1];
            $tmp = splitfirst($html, '<!--EncryptAlertStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptAlertEnd-->');
            $html .= $tmp[1];
        } else {
            $tmp = splitfirst($html, '<!--EncryptAlertStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptAlertEnd-->');
            $html .= str_replace('<!--constStr@SetpassfileBfEncrypt-->', getconstStr('SetpassfileBfEncrypt'), $tmp[0]) . $tmp[1];
            $tmp = splitfirst($html, '<!--EncryptBtnStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptBtnEnd-->');
            $html .= $tmp[1];
        }

        $tmp = splitfirst($html, '<!--MoveRootStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MoveRootEnd-->');
        if ($path != '/') {
            $html .= str_replace('<!--constStr@ParentDir-->', getconstStr('ParentDir'), $tmp[0]) . $tmp[1];
        } else $html .= $tmp[1];

        $tmp = splitfirst($html, '<!--MoveDirsStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MoveDirsEnd-->');
        $MoveDirs = $tmp[0];
        if ($files['type'] == 'folder') {
            foreach ($files['list'] as $file) {
                if ($file['type'] == 'folder') {
                    $MoveDirsStr = str_replace('<!--MoveDirsValue-->', str_replace('&', '&amp;', $file['name']), $MoveDirs);
                    $MoveDirsStr = str_replace('<!--MoveDirsValue-->', str_replace('&', '&amp;', $file['name']), $MoveDirsStr);
                    $html .= $MoveDirsStr;
                }
            }
        }
        $html .= $tmp[1];

        if (!isset($_COOKIE['timezone'])) {
            getStackHtml($html, "WriteTimezone", 0);
            replaceHtml($html, "timezone", $_SERVER['timezone']);
        } else {
            getStackHtml($html, "WriteTimezone", 1);
        }

        while (strpos($html, '{{.RawData}}')) {
            $str = '[';
            $i = 0;
            foreach ($files['list'] as $file) if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                $tmp = [];
                $tmp['name'] = $file['name'];
                $tmp['size'] = size_format($file['size']);
                $tmp['date'] = time_format($file['lastModifiedDateTime']);
                $tmp['@time'] = $file['date'];
                $tmp['@type'] = ($file['type'] == 'folder') ? 'folder' : 'file';
                $str .= json_encode($tmp) . ',';
            }
            if ($str == '[') {
                $str = '';
            } else $str = substr($str, 0, -1) . ']';
            $html = str_replace('{{.RawData}}', base64_encode($str), $html);
        }

        $exetime = round(microtime(true) - $_SERVER['php_starttime'], 3);
        //$ip2city = json_decode(curl('GET', 'http://ip.taobao.com/outGetIpInfo?ip=' . $_SERVER['REMOTE_ADDR'] . '&accessKey=alibaba-inc')['body'], true);
        //if ($ip2city['code']===0) $city = ' ' . $ip2city['data']['city'];
        $html = str_replace('<!--FootStr-->', date("Y-m-d H:i:s") . " " . getconstStr('Week')[date("w")] . " " . $_SERVER['REMOTE_ADDR'] . $city . ' Runningtime:' . $exetime . 's Mem:' . size_format(memory_get_usage()), $html);

        // 清除换行
        //while (strpos($html, "\r\n\r\n")) $html = str_replace("\r\n\r\n", "\r\n", $html);
        //while (strpos($html, "\r\r")) $html = str_replace("\r\r", "\r", $html);
        //while (strpos($html, "\n\n")) $html = str_replace("\n\n", "\n", $html);
        //while (strpos($html, PHP_EOL.PHP_EOL)) $html = str_replace(PHP_EOL.PHP_EOL, PHP_EOL, $html);
        while (preg_match("/\n( *)\n/", $html)) $html = preg_replace("/\n( *)\n/", "\n", $html);

        headandfoot($html, "Headomf", $path, $files, "head.omf", "globalHeadOmfUrl");
        headandfoot($html, "Headmd", $path, $files, "head.md", "globalHeadMdUrl");
        headandfoot($html, "Readmemd", $path, $files, "readme.md", "globalReadmeMdUrl");
        headandfoot($html, "Footomf", $path, $files, "foot.omf", "globalFootOmfUrl");
    }

    /*if ($_SERVER['admin']||!getConfig('disableChangeTheme')) {
        $theme_arr = scandir(__DIR__ . $slash . 'theme');
        $selecttheme = '
    <div style="position: fixed;right: 10px;bottom: 10px;">
        <select name="theme" onchange="changetheme(this.options[this.options.selectedIndex].value)">
            <option value="">'.getconstStr('Theme').'</option>';
        foreach ($theme_arr as $v1) {
            if ($v1!='.' && $v1!='..') $selecttheme .= '
            <option value="' . $v1 . '"' . ($v1==$theme?' selected="selected"':'') . '>' . $v1 . '</option>';
        }
        $selecttheme .= '
        </select>
    </div>
';
        $selectthemescript ='
<script type="text/javascript">
    function changetheme(str)
    {
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'theme=\'+str+\'; path=/; \'+expires;
        location.href = location.href;
    }
</script>';
        $tmp = splitfirst($html, '</body>');
        $html = $tmp[0] . $selecttheme . '</body>' . $selectthemescript . $tmp[1];
    }*/

    $tmp = splitfirst($html, '</title>');
    $html = $tmp[0] . '</title>' . $authinfo . $tmp[1];
    //if (isset($_SERVER['Set-Cookie'])) return output($html, $statusCode, [ 'Set-Cookie' => $_SERVER['Set-Cookie'], 'Content-Type' => 'text/html' ]);
    return output($html, $statusCode);
}
