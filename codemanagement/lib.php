<?php
defined('MOODLE_INTERNAL') || die();
/*************************************************************************************************/
/*** '课程下创建活动增删查改使用的接口'*/
function codemanagement_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

function codemanagement_add_instance($moduleinstance) {
    global $DB;
    
    $moduleinstance->timecreated = time();
    
    $moduleinstance->id = $DB->insert_record('codemanagement', $moduleinstance);
    
    return $moduleinstance->id;
}

function codemanagement_update_instance($moduleinstance, $mform = null) {
    global $DB;
    
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    
    return $DB->update_record('codemanagement', $moduleinstance);
}

function codemanagement_delete_instance($id) {
    global $DB;
    
    $exists = $DB->get_record('codemanagement', array('id' => $id));
    if (!$exists) {
        return false;
    }
    
    $DB->delete_records('codemanagement', array('id' => $id));
    
    return true;
}

function show_instance_information($moduleinstance){
    echo "<p><h2>" . $moduleinstance->name . "</h2></P>";
    if($moduleinstance->introformat==1){
        echo $moduleinstance->intro;
        echo "<br>";
    }
}

/************************************************************************************************/
/*** codemanagement_reponsitory '新增代码仓库操作函数'*/
function codemanagement_reponsitory_insert($fromform,$moduleinstance){
    global $DB, $USER;
    
    $reponsitory=new stdClass();
    $reponsitory->re_codemanagementid=$moduleinstance->id;
    $reponsitory->re_course_id=$moduleinstance->course;
    $reponsitory->re_instance_id=$moduleinstance->id;
    $reponsitory->re_userid=$USER->id;
    $reponsitory->re_name=$fromform->name;
    $reponsitory->re_description=$fromform->description;
    $reponsitory->re_date=time();
    $reponsitory->re_updatetime=time();
    $reponsitory->re_public=$fromform->private_public_id;
    
    $DB->insert_record('codemanagement_reponsitory', $reponsitory);
    echo("<script>console.log('data insert into the database')</script>");
}

/*** '代码仓库删除'*/
function codemanagement_reponsitory_delete($reponsitoryid,$id,$c){
    global $DB,$OUTPUT;
    $versionlist = $DB->get_records('codemanagement_ver_of_rep', array('reponsitory_id'=>$reponsitoryid));
    if(count($versionlist)>0){
        foreach ($versionlist as $version){
            $verseion = $DB->get_record('codemanagement_version', array('id'=>$version->version_id));
            $dir=$verseion->version_code_path;
            $filename=$verseion->version_code_name;
            $filepath=$dir.'/'.$filename;
            $zip = new ZipArchive();
            if ($zip->open($filepath) === true) {
                $zip->close();
                delDirAndFile($dir);
                if(true==$DB->delete_records('codemanagement_version', array('id'=>$version->version_id))){
                    if(true==$DB->delete_records('codemanagement_ver_of_rep', array('version_id'=>$version->version_id))){
                        //echo "delete success      ".$filepath;
                        if(true==$DB->delete_records('codemanagement_reponsitory', array('id'=>$reponsitoryid))){
                        }
                    }
                }
            }
        }
        redirect(new moodle_url('/mod/codemanagement/view.php', array('id'=>$id,'c'=>$c,'reponsitoryid'=>$reponsitoryid)));
        exit();
    }else{
        if(true==$DB->delete_records('codemanagement_reponsitory', array('id'=>$reponsitoryid))){
            redirect(new moodle_url('/mod/codemanagement/view.php', array('id'=>$id,'c'=>$c,'reponsitoryid'=>$reponsitoryid)));
            exit();
        }
    }
}

/*根据reponsitoryid获取re_name*/
function get_re_name($reponsitory){
    global $DB;
    
    $responsitory = $DB->get_record('codemanagement_reponsitory', array('id'=>$reponsitory));
    return $responsitory->re_name;
}

/*根据reponsitoryid获取re_description*/
function get_re_reponsitory($reponsitory){
    global $DB;
    
    $responsitory = $DB->get_record('codemanagement_reponsitory', array('id'=>$reponsitory));
    return $responsitory->re_description;
}

/*** '代码仓库更新'*/
function codemanagement_reponsitory_update($fromform,$reponsitoryid){
    global $DB;
    //，根据上传到到仓库代码的上传时间，更新仓库时间，以便根据仓库更新时间倒序打印使得老师便于查看最近更新的代码
    $DB->update_record('codemanagement_reponsitory', array('id'=>$reponsitoryid,'re_name'=>$fromform->name,'re_description'=>$fromform->description,'re_public'=>$fromform->private_public_id));
}

/*** '显示所有仓库信息'*/
function codemanagement_reponsitory_showtable($id, $c,$moduleinstance,$modulecontext)
{
    global $DB,$OUTPUT,$USER;
    //php中获取查询表单提交的查询条件值
    
    if (has_capability('mod/codemanagement:viewallreponsitory', $modulecontext)) {
        $reponsitorylist_result = $DB->get_records_sql('SELECT * FROM {codemanagement_reponsitory} where re_course_id='.$moduleinstance->course.' and re_instance_id='.$moduleinstance->id.' order by re_updatetime desc');
    }else{
        $reponsitorylist_result = $DB->get_records_sql('SELECT * FROM {codemanagement_reponsitory} where re_course_id='.$moduleinstance->course.' and re_instance_id='.$moduleinstance->id.' and ( re_userid='.$USER->id.' or re_public=2 ) order by re_updatetime desc');
    }
    
    //，获取所有仓库信息
    $count = count($reponsitorylist_result);
    echo "<p><h2>" . get_string('myreponsitorylist', 'codemanagement') . "</h2></P>";
    echo "<br>";
    
    if ($count > 0) {
        //echo "<form action=\"\" method=\"post\"><input type=\"text\" name=\"searchcondition\"><input type=\"submit\" value=".get_string('reponsitorysearchbutton', 'codemanagement') ."></form>";
        echo "<table class=\"table table-bordered table-hover\" >";
        echo "<thead>";
        echo "<tr>";
        echo "<th width=\"10%\">" . get_string('codemanagementusername', 'codemanagement') . "</th>";
        echo "<th width=\"16%\">" . get_string('reponsitoryname', 'codemanagement') . "</th>";
        echo "<th width=\"25%\">" . get_string('reponsitorydescription', 'codemanagement') . "</th>";
        echo "<th width=\"5%\">" . get_string('reponsitoryattribute', 'codemanagement') . "</th>";
        echo "<th width=\"13%\">" . get_string('reponsitorycreattime', 'codemanagement') . "</th>";
        echo "<th width=\"13%\">" . get_string('reponsitoryupdatetime', 'codemanagement') . "</th>";
        echo "<th width=\"18%\">" . get_string('reponsitoryoptions', 'codemanagement') . "</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($reponsitorylist_result as $reponsitorylist_result_item) {
            //，获取用户信息
            $userobject=$DB->get_record('user',array('id'=>$reponsitorylist_result_item->re_userid));
            echo "<tr>";
            echo "<td>".$userobject->lastname.$userobject->firstname.'<br>'.$userobject->username."</td>";
            echo "<td>" . $reponsitorylist_result_item->re_name . "</td>";
            echo "<td>" . $reponsitorylist_result_item->re_description . "</td>";
            if($reponsitorylist_result_item->re_public==1){
                echo "<td>" .get_string('private', 'codemanagement'). "</td>";
            }else{
                echo "<td>" .get_string('public', 'codemanagement'). "</td>";
            }
            echo "<td>" . date('Y-m-d H:i:s',$reponsitorylist_result_item->re_date) . "</td>";
            echo "<td>" . date('Y-m-d H:i:s',$reponsitorylist_result_item->re_updatetime) . "</td>";
            if($USER->id===$reponsitorylist_result_item->re_userid){
                echo "<td><a href=\"zipfileupload.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('reponsitoryuploadprojectfile', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('versionsofonereponsitory', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"updateresponsitory.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('updatereponsitory', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"deletecoderesponsitory.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('deleteversion', 'codemanagement') . "</a></td>";
            }else if(has_capability('mod/codemanagement:viewallreponsitory', $modulecontext)){
                echo "<td><a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('versionsofonereponsitory', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"deletecoderesponsitory.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('deleteversion', 'codemanagement') . "</a></td>";
            }else{
                echo "<td><a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitorylist_result_item->id\">" . get_string('versionsofonereponsitory', 'codemanagement') . "</a></td>";
            }
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        // ，仓库表中没有信息是提示
        echo $OUTPUT->notification(get_string('noreponsitorynotice', 'codemanagement'), 'notifysuccess');
    }
}

/***********************************************************************************************/
/*** '版本信息入库'*/
function  codemanagement_version_and_reponsitory_save($reponsitoryid,$fromform,$storage,$codezipfilename){
    global $DB,$USER;
    
    $filepath=$storage.'/'.$codezipfilename;
    $zip = new ZipArchive();
    $res = $zip->open($filepath);
    if($res){
        if ($zip->extractTo($storage)) {
            
            //，版本信息入库
            $version=new stdClass();
            $version->version_descrition=$fromform->description;
            $version->version_date=time();
            $version->version_code_path=$storage;
            $version->version_code_name=$codezipfilename;
            $version->version_user_id=$USER->id;
            $version->id=$DB->insert_record('codemanagement_version',$version);
            
            //，版本--仓库关系入库
            $version_of_reponsitory=new stdClass();
            $version_of_reponsitory->version_id=$version->id;
            $version_of_reponsitory->reponsitory_id=$reponsitoryid;
            $version_of_reponsitory->id=$DB->insert_record('codemanagement_ver_of_rep',$version_of_reponsitory);
            
            //，根据上传到到仓库代码的上传时间，更新仓库时间，以便根据仓库更新时间倒序打印使得老师便于查看最近更新的代码
            $DB->update_record('codemanagement_reponsitory', array('id'=>$reponsitoryid,'re_updatetime'=>time()));
            
        }
    }
}

/*** '上传zip文件相关函数'*/

function make_version_storage(){
    return make_unique_writable_directory(make_upload_directory("codeversion_list"));
}

/***********************************************************************************************/
/*** get the same dir structer of one version*/
function getDirAndFile($temp_dir,$dir_of_zip,$dir_index)
{
    
    $zip_file_path=$temp_dir.'/'.$dir_of_zip;
    
    $zip = new ZipArchive();
    $res = $zip->open($zip_file_path);
    if ($res == true) {
        $selectedfile=$zip->getNameIndex($dir_index);
    }
    $zip->close();
    
    $wait_print_dir_name=getDirName($temp_dir,$selectedfile);
    //$wait_print_dir_name = iconv("UTF-8","gb2312",$wait_print_dir_name);
    
    $str_file=substr($selectedfile,0,strlen($selectedfile)-1);
    
    $structure=array();
    $dirs=array();
    $files=array();
    
    if ( $handle = opendir("$wait_print_dir_name" ) ) {
        $i=0;
        while ( false !== ( $item = readdir( $handle ) ) ) {
            //$item=iconv("UTF-8","gb2312",$item);
            
            $chinesitem=mb_convert_encoding ($item,'UTF-8','gb2312');
            
            if ( $item != "." && $item != ".." ) {
                if ( is_dir( "$wait_print_dir_name/$item" ) ) {
                    $dir_object=new stdClass();
                    $dir_object->pathname="$item";
                    $dir_object->is_dir=1;
                    $dir_object->compare_pathname="$str_file/$item/";
                    
                    $fp = get_file_packer('application/zip');
                    $zip_files_list = $fp->list_files($zip_file_path);
                    if(!empty($zip_files_list)){
                        foreach ($zip_files_list as $file){
                            if(0===strcmp($dir_object->compare_pathname,$file->pathname)){
                                $dir_object->object_of_zip=$file;
                                break;
                            }
                        }
                    }
                    
                    $dirs[]=$dir_object;
                } else {
                    $file_object=new stdClass();
                    $file_object->pathname="$item";
                    $file_object->is_dir=0;
                    $file_object->compare_pathname="$str_file/$item";
                    
                    $fp = get_file_packer('application/zip');
                    $zip_files_list = $fp->list_files($zip_file_path);
                    if(!empty($zip_files_list)){
                        foreach ($zip_files_list as $file){
                            if(0===strcmp($file_object->compare_pathname,$file->pathname)){
                                $file_object->object_of_zip=$file;
                                break;
                            }
                        }
                    }
                    $files[]=$file_object;
                }
            }
        }
        closedir( $handle );
    }
    $structure=array_merge($dirs,$files);
    return $structure;
}

/*获取文件名称*/
function getDirName($dir,$selectedfile){
    $selectedfile_name_expected_char= substr($selectedfile,0,strlen($selectedfile)-1);
    return $dir.'/'.$selectedfile_name_expected_char;
}

/*获取文件名路径最后一个字符串*/
function getLastFileNameFormAPath($file_path_name){
    $dir_array=explode("/", $file_path_name);
    $num=count($dir_array);
    return $dir_array[$num-1];
}

/*在线预览打印td元素公用函数*/
function print_cell_local($alignment = 'center', $text = '&nbsp;', $class = 'cell') {
    $class = ' class="' . $class . '"';
    echo '<td align="' . $alignment . '" style="white-space:nowrap "' . $class . '>' . $text . '</td>';
}

/*在线预览项目文件目录中选中的文件*/
function view_one_versioncode_file_online($versionid,$reponsitoryid,$id,$c,$fileid,$codelisttype){
    global $DB,$OUTPUT;
    if($codelisttype===2){
        $url="viewcodeonline.php";
    }else{
        $url="viewcodestructureonline.php";
    }
    $rul_string="<a href=\"".$url."?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&dir_index=0\">" . get_string('gotoviewonlinepage', 'codemanagement') . "</a>";
    
    //version表获取具体版本
    $verseion = $DB->get_record('codemanagement_version', array('id'=>$versionid));
    //根据version path到moodledata中取出zip中所有文件
    $dir=$verseion->version_code_path;
    $filename=$verseion->version_code_name;
    $filepath=$dir.'/'.$filename;
    
    // 根据id获取文件，后期递归到文件的时候会使用此值传参数获取文件内容
    $zip = new ZipArchive();
    $res = $zip->open($filepath);
    if ($res == true) {
        $selectedfile=$zip->getNameIndex($fileid);
    }
    $zip->close();
    if($codelisttype===1){
        /*父目录*/
        $sub_dir_modle= $selectedfile;
        $dir_array=explode("/", $sub_dir_modle);
        if(count($dir_array)>1){
            $parement_str=null;
            for($i=0;$i<count($dir_array)-1;$i++){
                $parement_str=$parement_str.$dir_array[$i].'/';
            }
            $fp = get_file_packer('application/zip');
            $zip_files_list = $fp->list_files($filepath);
            if(!empty($zip_files_list)){
                foreach ($zip_files_list as $file){
                    if(0===strcmp($parement_str,$file->pathname)){
                        $parent_file_object=$file;
                        $rul_string="<a href=\"viewcodestructureonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&dir_index=$parent_file_object->index\">".'Paraent Dir' . "</a>&nbsp;&nbsp;".$rul_string;
                        break;
                    }
                }
            }
        }
    }
    echo "<div align=\"right\">".$rul_string."</div>";
    echo "<hr style=\"height:1px;border:none;border-top:1px solid #555555;\">";
    echo "<div style=\"width: 98%;padding: 10px;border: 1px solid #ddd;\">";
    echo "<div style=\"width: 100%;height: 500px;overflow: auto;\">";
    
    //根据显示文件内容
    $waitforReadfile = $dir.'/'.$selectedfile;
    if(file_exists($waitforReadfile)){
        $fp = fopen($waitforReadfile, 'r'); // 打开文件
        if ($fp !== false) {
            // 输出文件内容
            $stringstr="";
            while (! feof($fp)) { // feof 检测是否已到达文件末尾 返回TRUE或者FALSE
                $str=fgets($fp);
                $stringstr=$stringstr.$str;
                // echo $count++.".\n"."\n".htmlspecialchars($str,ENT_QUOTES,"UTF-8") . '</br>';
                //                   echo $str. '</br>';
            }
        }
        fclose($fp);
    }
    
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
    $encoded = mb_detect_encoding($stringstr, $encode_arr);
    $stringstr = mb_convert_encoding($stringstr,"utf-8",$encoded);
    echo "<pre class=\"\"><code class=\"\" style=\"font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
	font-size: 1.1em;\">".htmlspecialchars($stringstr,ENT_QUOTES,"UTF-8")."</code></pre>";
    echo "</div>";
    echo "</div>";
}

/**在线一次性预览一个版本下所有的目录及文件*/
function view_the_versioncode_online($versionid,$reponsitoryid,$id,$c){
    global $DB,$OUTPUT;
    
    $verseion = $DB->get_record('codemanagement_version', array('id'=>$versionid));
    //根据version path到moodledata中取出zip中所有文件
    $dir=$verseion->version_code_path;
    $filename=$verseion->version_code_name;
    $filepath=$dir.'/'.$filename;
    //zip文件解压到zip文件所在的根目录
    
    $fp = get_file_packer('application/zip');
    $files = $fp->list_files($filepath);
    
    echo "<div align=\"right\"><a href=\"viewcodestructureonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&dir_index=0\">" . get_string('gotostandardviewpage', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitoryid\">" . get_string('gotoversionlistpage', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"downloadcode.php?id=$id&c=$c&versionid=$versionid\">" . get_string('downloadversion', 'codemanagement') . "</a></div>";
    
    if(empty($files)){
        //，该仓库中没有任何代码版本时，显示提示信息
        echo $OUTPUT->notification(get_string('noversioninreponsitorynotice', 'codemanagement'), 'notifysuccess');
    }
    //打印文件列表
    if(!empty($files)){
        echo "<div style=\"width: 100%;height: 100%;overflow: auto;\">";
        
        echo "<form action=\"index.php\" method=\"post\" id=\"dirform\">" . "<div>" .
            " <input type=\"hidden\" name=\"repoid\" value=\"" . $reponsitoryid . "\" />";
        
        
        echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" class=\"generaltable\" >" .
            "<tr>".
            "<th class=\"header\" scope=\"col\" width=\"70%\">". get_string('versionid', 'codemanagement')." :" .$verseion->id."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".get_string('versiondescription', 'codemanagement')." :" .$verseion->version_descrition."</th>" .
            "<th class=\"header\" scope=\"col\" width=\"10%\">". get_string('filesize', 'codemanagement')."</th>" .
            "<th class=\"header\" scope=\"col\" width=\"20%\">". date('Y-m-d H:i:s',$verseion->version_date)."</th>". "</tr>\n";
        
        foreach($files as $file){
            if(true==$file->is_directory&&0==$file->size){
                echo "<tr class=\"folder\">";
                //                 print_cell_local("left",$OUTPUT->pix_icon('f/parent', ''). get_string('versiondescription','codemanagement'));
                print_cell_local("left",$OUTPUT->pix_icon('f/folder', "test").$file->pathname);
                print_cell_local("left","");
                print_cell_local("left",date('Y-m-d H:i:s',$file->mtime));
                //print_cell_local("left",$OUTPUT->pix_icon("f/".$icon, $strfile));
                echo "</tr>";
            }
            
            if(false==$file->is_directory&&0<=$file->size){
                $icon = mimeinfo("icon", $file->pathname);
                echo "<tr class=\"file\">";
                print_cell_local("left",$OUTPUT->pix_icon('f/'.$icon, "test")."<a href=\"viewonecodefileonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&fileid=$file->index&codelisttype=2\">".getLastFileNameFormAPath($file->pathname) . "</a>");
                print_cell_local("left",($file->size/1000).'KB');
                print_cell_local("left",date('Y-m-d H:i:s',$file->mtime));
                echo "</tr>";
            }
        }
        echo "</table>";
        echo "</div></form>";
        echo "</div>";
    }
}

/*预览项目目录结构*/
function view_the_versioncode_structure_online($versionid,$reponsitoryid,$id,$c,$dir_index){
    global $DB,$OUTPUT;
    
    $verseion = $DB->get_record('codemanagement_version', array('id'=>$versionid));
    //根据version path到moodledata中取出zip中所有文件
    $dir=$verseion->version_code_path;
    $filename=$verseion->version_code_name;
    $filepath=$dir.'/'.$filename;
    
    //zip文件解压到zip文件所在的根目录
    echo "<div align=\"right\"><a href=\"viewcodeonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid\">" . get_string('gotoqueckviewpage', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitoryid\">" . get_string('gotoversionlistpage', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"downloadcode.php?id=$id&c=$c&versionid=$versionid\">" . get_string('downloadversion', 'codemanagement') . "</a></div>";
    
    $dirs_and_files=getDirAndFile($dir,$filename,$dir_index);
    
    if(empty($dirs_and_files)){
        //，该仓库中没有任何代码版本时，显示提示信息
        echo $OUTPUT->notification(get_string('noversioninreponsitorynotice', 'codemanagement'), 'notifysuccess');
    }
    
    //打印文件列表
    if(!empty($dirs_and_files)){
        echo "<div style=\"width: 100%;height: 100%;overflow: auto;\">";
        echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" class=\"generaltable\" >" .
            "<tr>".
            "<th class=\"header\" scope=\"col\" width=\"70%\">". get_string('versionid', 'codemanagement')." :" .$verseion->id."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".get_string('versiondescription', 'codemanagement')." :" .$verseion->version_descrition."</th>" .
            "<th class=\"header\" scope=\"col\" width=\"10%\">". get_string('filesize', 'codemanagement')."</th>" .
            "<th class=\"header\" scope=\"col\" width=\"20%\">". date('Y-m-d H:i:s',$verseion->version_date)."</th>". "</tr>\n";
        
        $parement_file_flag=0;
        for($i=0;$i<count($dirs_and_files);$i++){
            if($parement_file_flag===0){
                $sub_dir_modle=$dirs_and_files[$i]->compare_pathname;
                $sub_dir_modle=substr($sub_dir_modle,0,strlen($sub_dir_modle)-1);
                $dir_array=explode("/", $sub_dir_modle);
                if(count($dir_array)>2){
                    
                    $parement_str=null;
                    for($i=0;$i<count($dir_array)-2;$i++){
                        $parement_str=$parement_str.$dir_array[$i].'/';
                    }
                    
                    $fp = get_file_packer('application/zip');
                    $zip_files_list = $fp->list_files($filepath);
                    if(!empty($zip_files_list)){
                        foreach ($zip_files_list as $file){
                            if(0===strcmp($parement_str,$file->pathname)){
                                $parent_file_object=$file;
                                echo "<tr class=\"folder\">";
                                //print_cell_local("left",$OUTPUT->pix_icon('f/parent', ''). get_string('versiondescription','codemanagement'));
                                print_cell_local("left",$OUTPUT->pix_icon('f/parent', "test")."<a href=\"viewcodestructureonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&dir_index=$parent_file_object->index\">".'..' . "</a>");
                                print_cell_local("left","");
                                print_cell_local("left","");
                                //print_cell_local("left",$OUTPUT->pix_icon("f/".$icon, $strfile));
                                echo "</tr>";
                                
                                $parement_file_flag=1;
                                break;
                            }
                        }
                    }
                }
            }
            if($parement_file_flag===1){
                break;
            }
            
            
        }
        
        for($i=0;$i<count($dirs_and_files);$i++){
            
            if($dirs_and_files[$i]->is_dir===1){
                echo "<tr class=\"folder\">";
                $dir_file_index=$dirs_and_files[$i]->object_of_zip->index;
                //print_cell_local("left",$OUTPUT->pix_icon('f/parent', ''). get_string('versiondescription','codemanagement'));
                print_cell_local("left",$OUTPUT->pix_icon('f/folder', "test")."<a href=\"viewcodestructureonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&dir_index=$dir_file_index\">".$dirs_and_files[$i]->pathname . "</a>");
                print_cell_local("left","");
                print_cell_local("left",date('Y-m-d H:i:s',$dirs_and_files[$i]->object_of_zip->mtime));
                //print_cell_local("left",$OUTPUT->pix_icon("f/".$icon, $strfile));
                echo "</tr>";
            }
            
            if($dirs_and_files[$i]->is_dir===0){
                $icon = mimeinfo("icon", $dirs_and_files[$i]->pathname);
                echo "<tr class=\"file\">";
                $filed_id=$dirs_and_files[$i]->object_of_zip->index;
                print_cell_local("left",$OUTPUT->pix_icon('f/'.$icon, "test")."<a href=\"viewonecodefileonline.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionid&fileid=$filed_id&codelisttype=1\">".$dirs_and_files[$i]->pathname . "</a>");
                print_cell_local("left",($dirs_and_files[$i]->object_of_zip->size/1000).'KB');
                print_cell_local("left",date('Y-m-d H:i:s',$dirs_and_files[$i]->object_of_zip->mtime));
                echo "</tr>";
            }
            
        }
        echo "</table>";
        echo "</div>";
    }
}

/*************************************************************************************************/
/*下载zip文件功能函数--设置文件头--显示文件名及大小*/
function generate_download_header($filename, $contentlength) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header("Content-Transfer-Encoding: Binary");
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.$contentlength);
}

/*测试下载zip文件核心函数*/
function down_load_codeversion_zip_file($versionid){
    global $DB,$OUTPUT;
    $verseion = $DB->get_record('codemanagement_version', array('id'=>$versionid));
    
    $dir=$verseion->version_code_path;
    $filename=$verseion->version_code_name;
    
    $filepath=$dir.'/'.$filename;
    
    $zip = new ZipArchive();
    if ($zip->open($filepath) === true) {
        $contentlength = filesize($filepath);
        echo ("<script>console.log('filename:" . json_encode($filename) . "')</script>");
        echo ("<script>console.log('filepath:" . json_encode($filepath) . "')</script>");
        echo ("<script>console.log('contentlength:" . json_encode($contentlength) . "')</script>");
        generate_download_header($filename, $contentlength);
        ob_clean(); // 解决文件下载时错误：格式未知或文件损坏可能是这个原因导致的。
        flush(); // 同上。
        readfile($filepath);
        $zip->close();
        exit();
    }else{
        echo ("<script>console.log('opean:" . json_encode('opean fail') . "')</script>");
    }
}

/************************************************************************************************/
/*function delete All Files of one dictionary besides this dictionary*/
function delDirAndFile( $dirName )
{
    if ( $handle = opendir("$dirName" ) ) {
        while ( false !== ( $item = readdir( $handle ) ) ) {
            if ( $item != "." && $item != ".." ) {
                if ( is_dir( "$dirName/$item" ) ) {
                    delDirAndFile( "$dirName/$item" );
                } else {
                    if( unlink( "$dirName/$item" ) ){
                    }
                }
            }
        }
        closedir( $handle );
        if( rmdir( $dirName ) ){
        }
    }
}

/*删除仓库中的某个版本*/
function delect_codeversion_from_moodledata($versionid,$reponsitoryid,$id,$c){
    global $DB,$OUTPUT;
    $verseion = $DB->get_record('codemanagement_version', array('id'=>$versionid));
    $dir=$verseion->version_code_path;
    $filename=$verseion->version_code_name;
    $filepath=$dir.'/'.$filename;
    $zip = new ZipArchive();
    if ($zip->open($filepath) === true) {
        $zip->close();
        delDirAndFile($dir);
        if(true==$DB->delete_records('codemanagement_version', array('id'=>$versionid))){
            if(true==$DB->delete_records('codemanagement_ver_of_rep', array('version_id'=>$versionid))){
                //echo $OUTPUT->notification(get_string('noversioninreponsitorynotice', 'codemanagement'), 'notifysuccess');
                redirect(new moodle_url('/mod/codemanagement/codeversionslist.php', array('id'=>$id,'c'=>$c,'reponsitoryid'=>$reponsitoryid)));
                exit();
            }
        }
    }
}

/**显示一个仓库下的所有版本列表*/
function codemanagement_version_and_reponsitory_showtable($reponsitoryid,$id,$c,$modulecontext){
    global $DB,$OUTPUT,$PAGE,$USER;
    
    checkRadioJsfunction();
    $versionlist=$DB->get_records_sql('SELECT * FROM {codemanagement_ver_of_rep} where reponsitory_id=? order by version_id desc',array($reponsitoryid));
    // ，根据版本id的list集合到版本表里查找每个版本的详细信息
    $countversionlist = count($versionlist);
    echo "<p><h2>".get_string('myversionlist','codemanagement')."</h2></P>";
    echo "<br>";
    
    //form表单
    echo "<form action=\"/mod/codemanagement/viewdifferencebetweentwoversions.php\" method=\"get\">".
        " <input type=\"hidden\" name=\"reponsitoryid\" value=\"" . $reponsitoryid . "\" />\n" .
        " <input type=\"hidden\" name=\"id\" value=\"" . $id . "\" />\n" .
        " <input type=\"hidden\" name=\"c\" value=\"" . $c . "\" />\n" ;
    $responsitory=$DB->get_record('codemanagement_reponsitory',array('id'=>$reponsitoryid));
    if($USER->id===$responsitory->re_userid  ){
        echo "<div align=\"right\"><a href=\"zipfileupload.php?id=$id&c=$c&reponsitoryid=$reponsitoryid\">" . get_string('gotoversionuploadpage', 'codemanagement') . "</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"view.php?id=$id\">" . get_string('gotomainpage', 'codemanagement') . "</a><button type=\"submit\" class=\"btn btn-default btn-link\" style=\"margin-bottom:4px;\">".get_string('comparewitheachother', 'codemanagement')."</button></div>";
    }else{
        echo "<div align=\"right\"><a href=\"view.php?id=$id\">" . get_string('gotomainpage', 'codemanagement') . "</a><button type=\"submit\" class=\"btn btn-default btn-link\" style=\"margin-bottom:4px;\">".get_string('comparewitheachother', 'codemanagement')."</button></div>";
    }
    
    if ($countversionlist > 0) {
        echo "<table class=\"table table-bordered table-hover\">";
        echo "<thead>";
        echo "<tr>";
        echo "<th width=\"55%\">" . get_string('versiondescription', 'codemanagement') . "</th>";
        echo "<th width=\"20%\">" . get_string('versioncreattime', 'codemanagement') . "</th>";
        echo "<th width=\"15%\">" . get_string('reponsitoryoptions', 'codemanagement') . "</th>";
        echo "<th width=\"10%\">" . get_string('comparewitheachother', 'codemanagement') . "</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($versionlist as $versionlist_item) {
            
            $versionlist_item_item = $DB->get_record('codemanagement_version', array('id' => $versionlist_item->version_id));
            echo ("<script>console.log('versionlist:" . json_encode($versionlist_item_item) . "')</script>");
            echo "<tr>";
            echo "<td width=\"55%\">" . $versionlist_item_item->version_descrition . "</td>";
            echo "<td width=\"20%\">" . date('Y-m-d H:i:s',$versionlist_item_item->version_date) . "</td>";
            
            if($USER->id===$versionlist_item_item->version_user_id ){
                echo "<td width=\"15%\"><a href=\"viewcodestructureonline.php?id=$id&c=$c&versionid=$versionlist_item_item->id&reponsitoryid=$reponsitoryid&dir_index=0\">" . get_string('viewonline', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"downloadcode.php?id=$id&c=$c&versionid=$versionlist_item_item->id\">" . get_string('downloadversion', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"delectecodeversion.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionlist_item_item->id\">" . get_string('deleteversion', 'codemanagement') . "</a></td>";
            }else if(has_capability('mod/codemanagement:viewallreponsitory', $modulecontext)){
                echo "<td width=\"15%\"><a href=\"viewcodestructureonline.php?id=$id&c=$c&versionid=$versionlist_item_item->id&reponsitoryid=$reponsitoryid&dir_index=0\">" . get_string('viewonline', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"downloadcode.php?id=$id&c=$c&versionid=$versionlist_item_item->id\">" . get_string('downloadversion', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"delectecodeversion.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&versionid=$versionlist_item_item->id\">" . get_string('deleteversion', 'codemanagement') . "</a></td>";
            }else{
                echo "<td width=\"15%\"><a href=\"viewcodestructureonline.php?id=$id&c=$c&versionid=$versionlist_item_item->id&reponsitoryid=$reponsitoryid&dir_index=0\">" . get_string('viewonline', 'codemanagement') . "</a>&nbsp;&nbsp;<a href=\"downloadcode.php?id=$id&c=$c&versionid=$versionlist_item_item->id\">" . get_string('downloadversion', 'codemanagement') . "</a></td>";
            }
            echo "<td width=\"10%\">".choose_from_radio(array($versionlist_item_item->id=>null),'compare','checkRadioJsfunction()',null,true).choose_from_radio(array($versionlist_item_item->id=>null),'comparewith','checkRadioJsfunction()',null,true) ."</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</form>";
    } else {
        //，该仓库中没有任何代码版本时，显示提示信息
        echo $OUTPUT->notification(get_string('noversioninreponsitorynotice', 'codemanagement'), 'notifysuccess');
    }
}

/************************************************************************************************/
/*js控制一排单选按钮仅有一个被选中，防止无效对比发生*/
function checkRadioJsfunction(){
    
    $js_check_function="
	function checkRadioJsfunction(){
    	var compare_radio  = document.getElementsByName('compare');
        var radio2 = document.getElementsByName('comparewith');
        for(var i=0; i<compare_radio.length;i++){
                  if(compare_radio[i].checked){
                    compare_radio[i].disabled=false;
                    radio2[i].disabled=true;
                }
                  if(!compare_radio[i].checked){
                    radio2[i].disabled=false;
                }
        }
        for(var i=0; i<radio2.length;i++){
                  if(radio2[i].checked){
                    radio2[i].disabled=false;
                    compare_radio[i].disabled=true;
                }
                  if(!radio2[i].checked){
                    compare_radio[i].disabled=false;
                }
        }
    }";
    
    echo "<script>".$js_check_function."</script>";
}

/*版本差异性对比radio单选构造函数*/
function choose_from_radio($options, $name, $onclick = '', $checked = '', $return = false) {
    
    static $idcounter = 0;
    
    if (!$name) {
        $name = 'unnamed';
    }
    
    $output = '<span class="radiogroup ' . $name . "\">\n";
    
    if (!empty($options)) {
        $currentradio = 0;
        foreach ($options as $value => $label) {
            $htmlid = 'auto-rb' . sprintf('%04d', ++$idcounter);
            $output .= ' <span class="radioelement ' . $name . ' rb' . $currentradio . "\">";
            $output .= '<input name="' . $name . '" id="' . $htmlid . '" type="radio" value="' . $value . '"';
            if ($value == $checked) {
                $output .= ' checked="checked"';
            }
            if ($onclick) {
                $output .= ' onclick="' . $onclick . '"';
            }
            if ($label === '') {
                $output .= ' /> <label for="' . $htmlid . '">' . $value . '</label></span>' . "\n";
            } else {
                $output .= ' /> <label for="' . $htmlid . '">' . $label . '</label></span>' . "\n";
            }
            $currentradio = ($currentradio + 1) % 2;
        }
    }
    
    $output .= '</span>' . "\n";
    
    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}

/*获取两个项目zip包中不同的文件*/
function get_different_files_form_two_versions($reponsitoryid,$id,$c,$compare,$comparewith,$versionid,$versionid_file_index,$versionid_comparewith,$versionid_comparewith_file_index,$is_add_or_delete){
    global $DB,$OUTPUT;
    
    if($compare!=-1 && $comparewith!=-1){
        
        $verseion_compare = $DB->get_record('codemanagement_version', array('id'=>$compare));
        $dir_compare=$verseion_compare->version_code_path;
        $filename_compare=$verseion_compare->version_code_name;
        $filepath_compare=$dir_compare.'/'.$filename_compare;
        
        $verseion_comparewith = $DB->get_record('codemanagement_version', array('id'=>$comparewith));
        $dir_comparewith=$verseion_comparewith->version_code_path;
        $filename_comparewith=$verseion_comparewith->version_code_name;
        $filepath_comparewith=$dir_comparewith.'/'.$filename_comparewith;
        
        //提取差异性文件并保存
        $different_files=array();
        $different_files_between_two_versions_md5_files=array();
        $different_files_between_two_versions=array();
        $different_files_between_two_versions_v2=array();
        $the_same_files_between_two_versions=array();
        
        $fp = get_file_packer('application/zip');
        $zip_files_list_compare = $fp->list_files($filepath_compare);
        $zip_files_list_comparewith = $fp->list_files($filepath_comparewith);
        
        if(!empty($zip_files_list_compare)){
            foreach ($zip_files_list_compare as $file_compare){
                $flag=0;
                if(!$file_compare->is_directory){
                    if(!empty($zip_files_list_comparewith)){
                        foreach ($zip_files_list_comparewith as $file_comparewith){
                            if(0===strcmp($file_compare->pathname,$file_comparewith->pathname)){
                                $flag=1;
                                
                                $md5filecompare=md5_file($dir_compare.'/'.$file_compare->pathname);
                                $md5filecomparewith=md5_file($dir_comparewith.'/'.$file_comparewith->pathname);
                                if($md5filecompare!=$md5filecomparewith){
                                }
                                /*原用size比较两个文件大小是否相同*/
                                //                              if($file_compare->size!=$file_comparewith->size){
                                /*现用md5_file（）函数判断文件是否相同*/
                                if($md5filecompare!=$md5filecomparewith){
                                    //现有文件改变
                                    $difference=new stdClass();
                                    $difference->file=$file_compare;
                                    $difference->versionid=$compare;
                                    $difference->versionid_file_index=$file_compare->index;
                                    $difference->versionid_comparewith=$comparewith;
                                    $difference->versionid_comparewith_file_index=$file_comparewith->index;
                                    $difference->is_add_or_delete=0;
                                    $different_files_between_two_versions[]=$difference;
                                    break;
                                }else{
                                    //文件没有改变
                                    $same=new stdClass();
                                    $same->file=$file_compare;
                                    $same->versionid=$compare;
                                    $same->versionid_comparewith=$comparewith;
                                    $same->is_add_or_delete=0;
                                    $the_same_files_between_two_versions[]=$same;
                                    break;
                                }
                            }
                        }
                        if(0==$flag){
                            //文件新增或删除
                            $difference=new stdClass();
                            $difference->file=$file_compare;
                            $difference->versionid=$compare;
                            $difference->versionid_file_index=$file_compare->index;
                            $difference->versionid_comparewith=$comparewith;
                            $difference->versionid_comparewith_file_index=-1;
                            $difference->is_add_or_delete=1;
                            $different_files_between_two_versions[]=$difference;
                        }
                    }
                }
            }
        }
        if(!empty($zip_files_list_comparewith)){
            foreach ($zip_files_list_comparewith as $file_comparewith){
                if(!$file_comparewith->is_directory){
                    $flag=0;
                    foreach ($different_files_between_two_versions as $file_comparewith_difference){
                        if(0===strcmp($file_comparewith->pathname,$file_comparewith_difference->file->pathname)){
                            $flag=1;
                            break;
                        }
                    }
                    if(0==$flag){
                        foreach ($the_same_files_between_two_versions as $file_comparewith_difference){
                            if(0===strcmp($file_comparewith->pathname,$file_comparewith_difference->file->pathname)){
                                $flag=1;
                                break;
                            }
                        }
                    }
                    if(0==$flag){
                        //另一个版本文件中遍历新增或者删除的文件
                        $difference=new stdClass();
                        $difference->file=$file_comparewith;
                        $difference->versionid=$comparewith;
                        $difference->versionid_file_index=$file_comparewith->index;
                        $difference->versionid_comparewith=$compare;
                        $difference->versionid_comparewith_file_index=-1;
                        $difference->is_add_or_delete=1;
                        $different_files_between_two_versions_v2[]=$difference;
                    }
                }
            }
        }
        $different_files=array_merge($different_files_between_two_versions,$different_files_between_two_versions_v2);
        //对比文件
        view_different_between_two_files($different_files,$reponsitoryid,$id,$c,$compare,$comparewith,$versionid,$versionid_file_index,$versionid_comparewith,$versionid_comparewith_file_index,$is_add_or_delete);
    }else{
        echo "<div align=\"right\"><a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitoryid\">" . get_string('gotoversionlistpage', 'codemanagement') . "</a></div>";
        echo $OUTPUT->notification(get_string('notselecttwocompareversionsnotice', 'codemanagement'), 'notifysuccess');
    }
}

/*对比预览两个版本中差异性文件*/
function view_different_between_two_files($different_files,$reponsitoryid,$id,$c,$compare,$comparewith,$versionid,$versionid_file_index,$versionid_comparewith,$versionid_comparewith_file_index,$is_add_or_delete){
    global $DB,$OUTPUT;
    /*打印差异性文件列表*/
    echo "<div align=\"right\"><a href=\"codeversionslist.php?id=$id&c=$c&reponsitoryid=$reponsitoryid\">" . get_string('gotoversionlistpage', 'codemanagement') . "</a></div>";
    
    echo "<div style=\"width: 100%;height: 220px;overflow: auto;\">";
    
    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" class=\"generaltable\" >" .
        "<tr>".
        "<th class=\"header\" scope=\"col\">". get_string('differentfilesfromtwocompareedversion', 'codemanagement')." :" .""."</th>" ."</tr>\n";
    // "<th class=\"header\" scope=\"col\">". get_string('versiondescription', 'codemanagement')." :" .""."</th>" .
    // "<th class=\"header\" scope=\"col\">". ""."</th>". "</tr>\n";
    
    if(!empty($different_files)){
        foreach ($different_files as $different_file){
            $icon = mimeinfo("icon", $different_file->file->pathname);
            echo "<tr class=\"file\">";
            print_cell_local("left",$OUTPUT->pix_icon('f/'.$icon, "test")."<a href=\"viewdifferencebetweentwoversions.php?id=$id&c=$c&reponsitoryid=$reponsitoryid&compare=$compare&comparewith=$comparewith&versionid=$different_file->versionid&versionid_file_index=$different_file->versionid_file_index&versionid_comparewith=$different_file->versionid_comparewith&versionid_comparewith_file_index=$different_file->versionid_comparewith_file_index&is_add_or_delete=$different_file->is_add_or_delete\">".$different_file->file->pathname . "</a>");
            //print_cell_local("left",($different_file->file->size/1000).'KB');
            //print_cell_local("left",date('Y-m-d H:i:s',$different_file->file->mtime));
            echo "</tr>";
        }
    }else{
        echo $OUTPUT->notification(get_string('nodifferencebetweentwoversions', 'codemanagement'), 'notifysuccess');
    }
    
    echo "</table>";
    echo "</div>";
    echo "<hr style=\"height:1px;border:none;border-top:1px solid #555555;\">";
    
    if($versionid!==-1){
        /*主对比版本*/
        $verseion_compare = $DB->get_record('codemanagement_version', array('id'=>$versionid));
        $dir_compare=$verseion_compare->version_code_path;
        $filename_compare=$verseion_compare->version_code_name;
        $filepath_compare=$dir_compare.'/'.$filename_compare;
        $zip = new ZipArchive();
        $res = $zip->open($filepath_compare);
        if ($res == true) {
            $selectedcomparefile=$zip->getNameIndex($versionid_file_index);
        }
        $zip->close();
        
        
        $verseion_comparewith = $DB->get_record('codemanagement_version', array('id'=>$versionid_comparewith));
        $dir_comparewith=$verseion_comparewith->version_code_path;
        $filename_comparewith=$verseion_comparewith->version_code_name;
        $filepath_comparewith=$dir_comparewith.'/'.$filename_comparewith;
        
        if($is_add_or_delete===0){
            $zip = new ZipArchive();
            $res = $zip->open($filepath_comparewith);
            if ($res == true) {
                $selectedcomparewithfile=$zip->getNameIndex($versionid_comparewith_file_index);
            }
            $zip->close();
        }
    }
    
    /*对比显示两个差异的文件*/
    echo "<div class=\"wiki-diff-container clearfix\" style=\"width: 100%;margin: 10px auto;\">";
    
    echo "<div class=\"wiki-diff-leftside\" style=\"width: 49.5%;margin: 0;padding: 0;float: left;\">";
    echo "<div class=\"wiki-diff-heading header clearfix\" style=\"padding: 10px;border: 1px solid #ddd;\">";
    if($versionid!==-1){
        echo "<div align=\"left\">"."Des:     ".$verseion_compare->version_descrition."</div>";
        echo "<div align=\"right\">"."<a href=\"viewcodestructureonline.php?id=$id&c=$c&versionid=$verseion_compare->id&reponsitoryid=$reponsitoryid&dir_index=0\">" . get_string('viewallversiononline', 'codemanagement') . "</a></div>";
    }else{
        echo "version id :34";
    }
    echo "</div>";
    echo "<div class=\"no-overflow\" style=\"padding: 10px;border: 1px solid #ddd;\">";
    echo "<div class=\"text_to_html\">";
    if($versionid!==-1){
        $waitforReadfile = $dir_compare.'/'.$selectedcomparefile;
        if(file_exists($waitforReadfile)){
            $fp = fopen($waitforReadfile, 'r'); // 打开文件
            if ($fp !== false) {
                // 输出文件内容
                $stringstri="";
                while (! feof($fp)) { // feof 检测是否已到达文件末尾 返回TRUE或者FALSE
                    $str=fgets($fp);
                    $stringstri=$stringstri.$str;
                }
            }
            fclose($fp);
        }
    }
    
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
    $encoded = mb_detect_encoding($stringstri, $encode_arr);
    $stringstri = mb_convert_encoding($stringstri,"utf-8",$encoded);
    //$stringstr=htmlspecialchars($stringstr,ENT_QUOTES,"UTF-8");
    echo "<pre class=\"\"><code class=\"\" style=\"font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
	font-size:1em\">".htmlspecialchars($stringstri,ENT_QUOTES,"UTF-8")."</code></pre>";
    
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class=\"wiki-diff-rightside\" style=\"width: 49.5%;margin: 0;padding: 0;float: left;margin-left: 1%;\">";
    echo "<div class=\"wiki-diff-heading header clearfix\" style=\"padding: 10px;border: 1px solid #ddd;\">";
    if($versionid!==-1){
        echo "<div align=\"left\">"."Des:     ".$verseion_comparewith->version_descrition."</div>";
        echo "<div align=\"right\">"."<a href=\"viewcodestructureonline.php?id=$id&c=$c&versionid=$verseion_comparewith->id&reponsitoryid=$reponsitoryid&dir_index=0\">" . get_string('viewallversiononline', 'codemanagement') . "</a></div>";
    }else{
        echo "version id :35";
    }
    echo "</div>";
    echo "<div class=\"no-overflow\" style=\"padding: 10px;border: 1px solid #ddd;\">";
    echo "<div class=\"text_to_html\" >";
    if($versionid!==-1&&$is_add_or_delete===0){
        $waitforReadfile = $dir_comparewith.'/'.$selectedcomparewithfile;
        if(file_exists($waitforReadfile)){
            $fp = fopen($waitforReadfile, 'r'); // 打开文件
            if ($fp !== false) {
                // 输出文件内容
                $stringstr="";
                while (! feof($fp)) { // feof 检测是否已到达文件末尾 返回TRUE或者FALSE
                    $str=fgets($fp);
                    $stringstr=$stringstr.$str;
                }
            }
            fclose($fp);
        }
    }
    
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
    $encoded = mb_detect_encoding($stringstr, $encode_arr);
    $stringstr = mb_convert_encoding($stringstr,"utf-8",$encoded);
    //$stringstr=htmlspecialchars($stringstr,ENT_QUOTES,"UTF-8");
    echo "<pre class=\"\"><code class=\"\" style=\"font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
	font-size: 1em;\">".htmlspecialchars($stringstr,ENT_QUOTES,"UTF-8")."</code></pre>";
    
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
}