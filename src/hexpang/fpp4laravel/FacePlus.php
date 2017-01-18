<?php
/**
 * User: hexpang
 * Date: 2017/1/18
 * Time: 6:55
 */
namespace hexpang\fpp4laravel;
use hexpang\APIHelper\Helper as APIHelper;
class FacePlus{
    var $helper;
    var $api_key;
    var $api_secret;
    private static $instance;

    /**
     * 单例
     * @return FacePlus
     */
    public static function sharedDefaults(){
        if(!FacePlus::$instance){
            FacePlus::$instance = new FacePlus();
        }
        return FacePlus::$instance;
    }

    /**
     * FacePlus constructor.
     */
    public function __construct(){
        $this->api_key = env('FPP_API_KEY','your-api-key');
        $this->api_secret = env('FPP_API_SECRET','your-api-secret');
        $this->helper = new APIHelper('https://api-cn.faceplusplus.com/facepp/v3/');
    }

    /**
     * 创建人脸集合
     * @param string $display_name
     * @param string $outer_id
     * @param string $tags
     * @param string $face_tokens
     * @param string $user_data
     * @param int $force_merge
     * @return mixed
     */
    public function FaceSetCreate($display_name='',$outer_id='',$tags='',$face_tokens='',$user_data='',$force_merge=0){
        $data = ['display_name'=>$display_name,'outer_id'=>$outer_id,'tags'=>$tags,'face_tokens'=>$face_tokens,'user_data'=>$user_data,'force_merge'=>$force_merge];
        return $this->execute('faceset/create',$data);
    }

    /**
     * 删除集合
     * @param string $faceset_token
     * @param string $outer_id
     * @param int $check_empty
     * @return mixed
     */
    public function FaceSetDelete($faceset_token='',$outer_id='',$check_empty=1){
        return $this->execute('faceset/delete',['faceset_token'=>$faceset_token,'outer_id'=>$outer_id,'check_empty'=>$check_empty]);
    }

    /**
     * 获取所有集合
     * @param string $tags
     * @param int $start
     * @return mixed
     */
    public function FaceSets($tags='',$start=1){
        return $this->execute('faceset/getfacesets',['tags'=>$tags,'start'=>$start]);
    }

    /**
     * 更新集合
     * @param string $faceset_token
     * @param string $outer_id
     * @param string $new_outer_id
     * @param string $display_name
     * @param string $tags
     * @param string $user_data
     * @return mixed
     */
    public function FaceSetUpdate($faceset_token='',$outer_id='',$new_outer_id='',$display_name='',$tags='',$user_data=''){
        $data = ['display_name'=>$display_name,'outer_id'=>$outer_id,'tags'=>$tags,'faceset_token'=>$faceset_token,'user_data'=>$user_data,'new_outer_id'=>$new_outer_id];
        return $this->execute('faceset/update',$data);
    }

    /**
     * 获取集合信息
     * @param string $faceset_token
     * @param string $outer_id
     * @return mixed
     */
    public function FaceSetDetail($faceset_token='',$outer_id=''){
        return $this->execute('faceset/getdetail',['faceset_token'=>$faceset_token,'outer_id'=>$outer_id]);
    }

    /**
     * 增加人脸到集合中
     * @param $face_tokens
     * @param string $faceset_token
     * @param string $outer_id
     * @return mixed
     */
    public function FaceSetAddFace($face_tokens,$faceset_token='',$outer_id=''){
        return $this->execute('faceset/addface',['face_tokens'=>$face_tokens,'faceset_token'=>$faceset_token,'outer_id'=>$outer_id]);
    }

    /**
     * 从集合中删除人脸信息
     * @param $face_tokens
     * @param string $faceset_token
     * @param string $outer_id
     * @return mixed
     */
    public function FaceSetRemoveFace($face_tokens,$faceset_token='',$outer_id=''){
        return $this->execute('faceset/removeface',['face_tokens'=>$face_tokens,'faceset_token'=>$faceset_token,'outer_id'=>$outer_id]);
    }

    /**
     * 执行请求
     * @param $action
     * @param $data
     * @return mixed
     */
    private function execute($action,$data){
        foreach ($data as $k=>$v){
            if(empty($v)){
                unset($data[$k]);
            }
        }
        $data['api_key'] = $this->api_key;
        $data['api_secret'] = $this->api_secret;
        return $this->helper->Post($action,$data);
    }

    /**
     * 检查是否为URL
     * @param $str
     * @return bool
     */
    private function isURL($str){
        if (!preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i',$str)) {
            return false;
        }
        return true;
    }

    /**
     * 人脸搜索
     * @param $face
     * @param string $faceset_token
     * @param string $outer_id
     * @param int $result_count
     * @return mixed
     */
    public function search($face,$faceset_token = '',$outer_id = '',$result_count = 1){
        $data = ['return_result_count'=>$result_count];
        if(is_file($face)){
            $data['image_file'] = new \CURLFile($face);
        }else if($this->isURL($face)){
            $data['image_url'] = $face;
        }else{
            $data['face_token'] = $face;
        }
        if($faceset_token){
            $data['faceset_token'] = $faceset_token;
        }else{
            $data['outer_id'] = $outer_id;
        }
        return $this->execute('search',$data);
    }

    /**
     * 人脸比对
     * @param $face1
     * @param $face2
     * @return mixed
     */
    public function compare($face1,$face2){
        $data = [];
        if(is_file($face1)){
            $data['image_file1'] = new \CURLFile($face1);
        }else if($this->isURL($face1)){
            $data['image_url1'] = $face1;
        }else{
            $data['face_token1'] = $face1;
        }
        if(is_file($face2)){
            $data['image_file2'] = new \CURLFile($face2);
        }else if($this->isURL($face2)){
            $data['image_url2'] = $face2;
        }else{
            $data['face_token2'] = $face2;
        }
        return $this->execute('compare',$data);
    }

    /**
     * 进行分析
     * @param $image
     * @param int $landmark
     * @param string $attributes
     * @return mixed
     */
    public function detect($image,$landmark=0,$attributes='none'){
        $data = ['return_landmark'=>$landmark,'return_attributes'=>$attributes];
        if(!is_file($image)){
            $data['image_url'] = $image;
        }else{
            $data['image_file'] = new \CURLFile(realpath($image));
        }
        return $this->execute('detect',$data);
    }
}
?>
