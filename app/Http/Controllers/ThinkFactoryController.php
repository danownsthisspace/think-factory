<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;;
use Illuminate\Support\Facades\Log;

class ThinkFactoryController extends Controller
{

    private function createThinkFactoryId() {
        $thinkFactoryId = "think_factory_" . substr(md5(microtime()),rand(0,26), 4);
        
        if (Redis::exists($thinkFactoryId)) {
            return $this->createThinkFactoryId();
        }
        return $thinkFactoryId;
    }

    public function createUserId() {
        return "user_" . uniqid();
    }

    public function create()
    {

        $userId     = request()->input('userId');

        $validatedData = request()->validate([
            'userId' => ['required'],
            'question' => ['required'],
            'answers' => ['required'],
        ]);

        $thinkFactoryId = $this->createThinkFactoryId();

        if (!$userId) {
            $userId = $this->createUserId();
        }

        $thinkFactory = [
            'factoryId' => str_replace("think_factory_", "", $thinkFactoryId),
            'creatorId' => $validatedData['userId'],
            'question' => trim($validatedData['question']),
        ];

        foreach($validatedData['answers'] as $i => $answer) {
            $answer = trim($answer);
            if (empty($answer)) {
                abort(400);
            }
            $thinkFactory["answer_${i}"] = $answer;
        }
        
        Redis::multi();
        Redis::hmset($thinkFactoryId, $thinkFactory);
        Redis::lpush($userId, $thinkFactoryId);
        Redis::expire($thinkFactoryId, env('REDIS_EXPIRE_TIME'));
        Redis::expire($userId, env('REDIS_EXPIRE_TIME'));
        Redis::exec();

        return  response()->json(["factoryId" => $thinkFactory['factoryId']]);
    }

    public function find($id) {
        $id = strtolower($id);
        $thinkFactory = Redis::hgetall("think_factory_" . $id);

        if (!$thinkFactory) {
            abort(404);
        }

        return $thinkFactory;
    }

    public function vote($id) {
        $id = strtolower($id);
        $answerToVoteFor = request()->input('answerKey');
        $userId         = request()->input('userId');

        $thinkFactoryId = "think_factory_" . $id;

        if (!Redis::exists($thinkFactoryId)) {
            abort(404);
        }

        if (Redis::sismember("voters_${thinkFactoryId}", $userId)) {
            abort(400);
        }

        Redis::multi();
        Redis::sadd("voters_${thinkFactoryId}", $userId);
        Redis::expire("voters_${thinkFactoryId}", env('REDIS_EXPIRE_TIME'));

        Redis::sadd("votes_${thinkFactoryId}_$answerToVoteFor", $userId);
        Redis::expire("votes_${thinkFactoryId}_$answerToVoteFor", env('REDIS_EXPIRE_TIME'));
        Redis::exec();

        return "success";
    }
    
    public function getVotes($id) {
        $id = strtolower($id);
        $thinkFactoryId = "think_factory_" . $id;

        if (!Redis::exists($thinkFactoryId)) {
            abort(404);
        }

        $thinkFactory = Redis::hgetall($thinkFactoryId);

        $answerCounts = [];
        
        foreach($thinkFactory as $key => $value) {
            if (Str::startsWith($key, "answer_")) {
                $answerCounts[$key] = Redis::scard("votes_${thinkFactoryId}_$key");
            }
        }

        return $answerCounts;
    }

    public function getUserResponse($id, $userId)
    {

        $thinkFactoryId = "think_factory_" . $id;
        if (Redis::sismember("voters_${thinkFactoryId}", $userId)) {
            $thinkFactory = Redis::hgetall($thinkFactoryId);
            foreach($thinkFactory as $key => $value) {
                if (Str::startsWith($key, "answer_") && Redis::sismember("votes_${thinkFactoryId}_$key",$userId)) {
                    return $key;
                }
            }
        }

        return "";
    }
}
