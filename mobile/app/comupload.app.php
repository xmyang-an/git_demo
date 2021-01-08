<?php

define('THUMB_WIDTH', 350);
define('THUMB_HEIGHT', 350);
define('THUMB_QUALITY', 85);

class ComuploadApp extends StoreadminbaseApp
{
    var $id = 0;
    var $belong = 0;
    var $store_id = 0;
    var $instance = null; //同一个模型可以设置多个不同实例（goods模型可以有商品相册或商品描述两个实例）
    function __construct()
    {
        $this->ComuploadApp();
    }

    function ComuploadApp()
    {
        parent::__construct();
        if (isset($_REQUEST['id']))
        {
             $this->id = intval($_REQUEST['item_id']);
        }
        if (isset($_REQUEST['belong']))
        {
            $this->belong = intval($_REQUEST['belong']);
        }
        /* 实例 */
        if (isset($_GET['instance']))
        {
            $this->instance = $_GET['instance'];
        }logresult1('text', $_REQUEST);
        $this->store_id = $this->visitor->get('manage_store');

    }

    function index()
    {
            import('image.func');
            import('uploader.lib');
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_GOODS_IMAGE); // 2M
            $upload_mod =& m('uploadedfile');
            /* 取得剩余空间（单位：字节），false表示不限制 */
            $store_mod  =& m('store');
            $settings   = $store_mod->get_settings($this->store_id);

            $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $upload_mod->get_file_size($this->store_id) : false;

            $files = $_FILES['file'];
            if ($files['error'] === UPLOAD_ERR_OK)
            {
                /* 处理文件上传 */
                $file = array(
                    'name'      => $files['name'],
                    'type'      => $files['type'],
                    'tmp_name'  => $files['tmp_name'],
                    'size'      => $files['size'],
                    'error'     => $files['error']
                );
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $data = current($uploader->get_error());
                    $this->json_error(Lang::get($data['msg']));
					exit;
                }
                /* 判断能否上传 */
                if ($remain !== false)
                {
                    if ($remain < $file['size'])
                    {
						$this->json_error(Lang::get('space_limit_arrived'));
						exit;
                    }
                }

                $uploader->root_dir(ROOT_PATH);
                $dirname = '';
                if ($this->belong == BELONG_GOODS)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store') . '/goods_' . (time() % 200);
                }
                elseif ($this->belong == BELONG_STORE)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store') . '/other';
                }
                elseif ($this->belong == BELONG_ARTICLE)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store').'/article';
                }
				elseif ($this->belong == BELONG_MEAL)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store').'/meal';
                }
				elseif ($this->belong == BELONG_GIFT)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store').'/gift';
                }

                $filename  = $uploader->random_filename();
                $file_path = $uploader->save($dirname, $filename);
                /* 处理文件入库 */
                $data = array(
                    'store_id'  => $this->store_id,
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'file_name' => $file['name'],
                    'file_path' => $file_path,
                    'belong'    => $this->belong,
                    'item_id'   => $this->id,
                    'add_time'  => gmtime(),
                );
                $file_id = $upload_mod->add($data);
                if (!$file_id)
                {
                    $data = $uf_mod->get_error();
					$this->json_error(Lang::get($data['msg']));
					exit;
                }

                if ($this->instance == 'goods_image') // 如果是上传商品相册图片
                {
                    /* 生成缩略图 */
                    $thumbnail = dirname($file_path) . '/small_' . basename($file_path);
                    make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH .'/' . $thumbnail, THUMB_WIDTH, THUMB_HEIGHT, THUMB_QUALITY);

                    /* 更新商品相册 */
                    $mod_goods_image = &m('goodsimage');
                    $goods_image = array(
                        'goods_id'   => $this->id,
                        'image_url'  => $file_path,
                        'thumbnail'  => $thumbnail,
                        'sort_order' => 255,
                        'file_id'    => $file_id,
                    );
                    if (!$mod_goods_image->add($goods_image))
                    {
                        $data = $this->mod_goods_imaged->get_error();
                        $this->json_error(Lang::get($data['msg']));
						exit;
                    }
                    $data['thumbnail'] = $thumbnail;

                }

                $data['instance'] = $this->instance;
                $data['file_id'] = $file_id;
                
				$this->json_result($data);
				exit;
            }
            elseif ($files['error'] == UPLOAD_ERR_NO_FILE)
            {
				$this->json_error(Lang::get('file_empty'));
				exit;
            }
            else
            {
				$this->json_error(Lang::get('sys_error'));
				exit;
            }
    }
}
?>