<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Avito\Support\Api\Review\ReplyToReview;

use BaksDev\Avito\Api\AvitoApi;

/**
 * Отправка ответа на отзыв
 * @see https://developers.avito.ru/api-catalog/ratings/documentation#operation/createReviewAnswerV1
 */
final class AvitoReplyToReviewRequest extends AvitoApi
{
    /**  ID отзыва */
    private int $avitoReview;

    public function avitoReview(string|int $avitoReview): self
    {
        if(is_string($avitoReview) && str_starts_with('AR-', $avitoReview))
        {
            $avitoReview = str_replace('AR-', '', $avitoReview);
        }


        $this->avitoReview = (int) $avitoReview;

        return $this;
    }

    /**  $message - Текст ответа на отзыв */
    public function send(string $message): bool
    {
        /**
         * Выполнять операции запроса ТОЛЬКО в PROD окружении
         */
        if($this->isExecuteEnvironment() === false)
        {
            return false;
        }


        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/ratings/v1/answers',
                [
                    "payload" => [
                        'message' => $message,
                        'reviewId' => $this->avitoReview
                    ]
                ]
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical($content['error']['code'].': '.$content['error']['message'], [self::class.':'.__LINE__]);

            return false;
        }

        if(empty($content))
        {
            return false;
        }

        return true;
    }
}
