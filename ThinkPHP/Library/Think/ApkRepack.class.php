<?php
// 打包推广包
namespace Think;
class ApkRepack{
	public $toolPath = 'D:\\backup\\tools';
	public $unpackPath = 'D:\\backup\\unpack_tmp';
	public $sign = "";
	
	public function __construct($_apkfile,$_outputfile="") {
		$mydir = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		$_apkfile = str_replace('\\', '/', $_apkfile);
		if($_outputfile==""){
			$_outputfile = $_apkfile.".repack.apk";
		}
		if(!strstr($_apkfile,":")){
			if($_apkfile[0]=='/'){
				$_apkfile = $mydir.$_apkfile;
			}else{
				$_apkfile = $mydir.'/'.$_apkfile;
			}
		}
		$_outputfile == str_replace('\\', '/', $_outputfile);
		if(!strstr($_outputfile,":")){
			if($_outputfile[0]=='/'){
				$_outputfile = $mydir.$_outputfile;
			}else{
				$_outputfile = $mydir.'/'.$_outputfile;
			}
		}
		
		$this->apkfile = $_apkfile;
		$this->outPath = $_outputfile;
		
		$fn = pathinfo($_apkfile);
		if(!is_file($_apkfile)){
			return false;
		}
		
		$this->unpackPath = str_replace('\\', '/', $this->unpackPath);
		$this->unpackPath .= '/'.$fn['filename'];
		//$this->sign = md5_file($this->apkfile);
		//$this->init();
		return true;
	}
	public function makeApkByUid($value){
		copy($this->apkfile,$this->outPath.".tmp");
		$zip = new \ZipArchive;
		if ($zip->open($this->outPath.".tmp") === TRUE) {
			$zip->addFromString('assets/WYR_PARENT.txt', $value);
			$zip->close();
		} else {
			return false;
		}
		//copy($this->outPath.".tmp",$this->outPath);
		$this->signPack($this->outPath);
		unlink($this->outPath.".tmp");
		return true;
	}
	//----------------------------------------------------
	//以下为内部功能实现，只供本类内部调用，无需关心
	//----------------------------------------------------
	
	//拆包
	private function unpack(){
		$cmdunpack = $this->toolPath.'\\jre\\bin\\java -jar '. $this->toolPath .'\\apktool.jar d -f '. $this->apkfile .' '. $this->unpackPath.'\\'.$this->sign;
		$ret = system($cmdunpack);exit;
		return $ret;
	}
	
	private function init(){
		if(!is_dir($this->unpackPath)){
			mkdir($this->unpackPath);
		}
		if(!is_dir($this->unpackPath.'/'.$this->sign)){
			mkdir($this->unpackPath.'/'.$this->sign);
		}
		if(!is_file($this->unpackPath.'/'.$this->sign .'/AndroidManifest.xml')){
			$this->unpack();
		}
	}
	//改AndroidManifest的Meta-data字段值
	private function makePackByMetaData($value,$metaname="ZHAOYOU_PARENT"){
		$xml = file_get_contents($this->unpackPath.'/'.$this->sign .'/AndroidManifest.xml' );
		$xml = preg_replace(
			'/<meta-data.*?'.$metaname.'.*?value="\d+".*?>/s',
			'<meta-data android:name="'.$metaname.'" android:value="'.$value.'"',
			$xml);
		file_put_contents($this->unpackPath.'/'.$this->sign .'/AndroidManifest.xml' ,$xml);
	}
	//打包
	public function repack(){
		$this->pack($this->outPath);
		$this->signPack($this->outPath);
		unlink($this->outPath.".tmp");
	}
	
	private function pack($_outPath){
		$cmdrepack = $this->toolPath.'\\jre\\bin\\java -jar '. $this->toolPath .'\\apktool.jar b -f '. $this->unpackPath.'/'.$this->sign .' '. $_outPath.'.tmp';
		$ret = system($cmdrepack);
		return $ret;
	}
	private function signPack($_outPath){
		$cmdsign   = $this->toolPath.'\\jre\\bin\\java -jar '. $this->toolPath. '\\signapk.jar '. $this->toolPath. '\\testkey.x509.pem '. $this->toolPath. '\\testkey.pk8 '. $_outPath .'.tmp  '.$_outPath;
		//echo $cmdsign;
		$ret = system($cmdsign);
		return $ret;
	}
	
}
?>