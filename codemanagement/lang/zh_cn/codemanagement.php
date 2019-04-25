<?php
$string['modulename']='代码管理';
$string['modulename_help']='代码管理，与github代码管理类似，该活动提供了项目代码上传下载、在线预览、以及同一个项目下两个版本之间的差异性对比功能，方便老师在线查看学生代码，追踪项目进度，为教学提供极大便利。同时方便学生管理自己的代码';
$string['modulenameplural']='代码管理';
$string['pluginname']='代码管理';
$string['pluginadministration']='代码管理';

//mod_form.php
$string['codemanagementname']='代码管理名称';
$string['responsitorydisplayorontdisplay']='是否显示代码管理活动简介';

//view.php\codeversonslist.php\zipfileupload.php\updateresponsitory.php
$string['missingidandcmid']='课程id及课程活动id';
$string['codemanagementreponsitoryinsert']='数据入库成功';
$string['codemanagementreponsitoryupdate']='数据更新成功';

//lib.php
$string['myreponsitorylist']='我的仓库列表';
$string['codemanagementusername']='所有者';
$string['reponsitoryname']='名称';
$string['reponsitorydescription']='描述';
$string['reponsitorycreattime']='创建时间';
$string['reponsitoryoptions']='操作';
$string['reponsitoryuploadprojectfile']='上传';
$string['reponsitorysearchbutton']='查询';
$string['noreponsitorynotice']='您还没有属于自己的代码仓库，请在下方创建';
$string['noversioninreponsitorynotice']='该仓库中还没有任何版本信息，请至版本上传页面上传代码';
$string['viewonline']='预览';
$string['downloadversion']='下载';
$string['deleteversion']='删除';
$string['versioncreattime']='创建时间';
$string['versiondescription']='版本描述';
$string['myversionlist']='版本列表';
$string['versionsofonereponsitory']='历史';
$string['gotoversionuploadpage']='上传新版本';
$string['gotomainpage']='返回主页';
$string['reponsitoryupdatetime']='更新时间';
$string['reponsitoryattribute']='属性';
$string['private']='私有';
$string['public']='公有';
$string['gotoversionlistpage']='返回版本列表';
$string['versionid']='版本id';
$string['gotoviewonlinepage']='返回文件列表预览页';
$string['gotoqueckviewpage']='快捷预览';
$string['gotostandardviewpage']='标准预览';
$string['notselecttwocompareversionsnotice']='请到版本列表页，正确选择两个待对比的版本，否则无法进行对比';
$string['nodifferencebetweentwoversions']='两个被选中的版本没有任何的差异';
$string['differentfilesfromtwocompareedversion']='两个版本中的所有差异文件列表 ';
$string['viewallversiononline']='预览整个版本';
$string['comparewitheachother']='版本对比';
$string['filesize']='文件大小';
$string['updatereponsitory']='修改';

//installfile_form.php
$string['installfromzip']='从zip中获取项目文件';
$string['installfromzip_help']='将项目文件以zip包的形式上传，压缩包文件名为项目名，不可重命名为其他名字，否则后期解压出错';
$string['installfromzipfile']='zip包';
$string['installfromzipfile_help']='不含.exe等可执行文件，及.jar文件，同时项目文件中的文件名不允许有中文出现否则后期无法在线预览';
$string['installfromzipsubmit']='从zip中获取项目文件';
$string['versiondescription']='版本说明';
$string['versiondescription_help']='版本说明时，说明更改或升级要点，以便版本回滚，字数不得超过255个字符，否则无法上传';

//create_reponsitory_form.php
$string['createreponsitory']='创建代码管理仓库';
$string['createreponsitory_help']='代码仓库用于存放一个项目的多个文件版本';
$string['reponsitoryname']='仓库名称';
$string['reponsitoryname_help']='填写仓库名称';
$string['responsitorysubmit']='创建代码仓库';
$string['intro']='简介';
$string['intro_help']='简介字符数不能超过255个字符，否则不能能提交';
$string['responsitoryprivateorpoublic']='选择仓库属性';
$string['responsitoryprivateorpoublic_help']='仓库的公有与私有属性，当仓库为private时，同学之间不可见，仓库为public时同学之间可见，无论是私有还是公有对于老师而言均可见。';

//access.php
//$string['codemanagement:addinstance']='addinstance';
//update_responsitory_form
$string['responsitoryupdatesubmit']='确认修改';

?>