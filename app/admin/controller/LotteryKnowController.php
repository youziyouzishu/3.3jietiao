<?php

namespace app\admin\controller;

use FFMpeg\FFMpeg;
use support\Request;
use support\Response;
use app\admin\model\LotteryKnow;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 知识讲座 
 */
class LotteryKnowController extends Crud
{
    
    /**
     * @var LotteryKnow
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new LotteryKnow;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('lottery-know/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $video = $request->post('video');
            $relative_path = str_replace('/app/admin', config('plugin.admin.app.public_path'), $video);
            // 创建 FFMpeg 实例
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => '/www/server/ffmpeg/ffmpeg-6.1/ffmpeg',
                'ffprobe.binaries' => '/www/server/ffmpeg/ffmpeg-6.1/ffprobe'
            ]);

            // 打开视频文件
            $media = $ffmpeg->open($relative_path);
            // 使用 FFProbe 获取视频时长（以秒为单位）
            $duration = $media->getStreams()->videos()->first()->get('duration');
            // 将时长转换为 h:i:s 格式
            $durationFormatted = gmdate('i:s', (int)$duration);
            $request->setParams('post',[
                'duration'=>$durationFormatted
            ]);
            return parent::insert($request);
        }
        return view('lottery-know/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $video = $request->post('video');
            $relative_path = str_replace('/app/admin', config('plugin.admin.app.public_path'), $video);
            // 创建 FFMpeg 实例
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => '/www/server/ffmpeg/ffmpeg-6.1/ffmpeg',
                'ffprobe.binaries' => '/www/server/ffmpeg/ffmpeg-6.1/ffprobe'
            ]);

            // 打开视频文件
            $media = $ffmpeg->open($relative_path);
            // 使用 FFProbe 获取视频时长（以秒为单位）
            $duration = $media->getStreams()->videos()->first()->get('duration');
            // 将时长转换为 h:i:s 格式
            $durationFormatted = gmdate('i:s', (int)$duration);
            $request->setParams('post',[
                'duration'=>$durationFormatted
            ]);
            return parent::update($request);
        }
        return view('lottery-know/update');
    }

}
