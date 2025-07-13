<?php

namespace App\Response;

class ListResponse extends ApiResponse
{
    public static function collection(
        array $lists,
        ?string $message = 'Lists retrieved successfully'
    ): self {
        $listData = array_map(
            fn($list) => $list->toArray(),
            $lists
        );

        return new self(
            success: true,
            data: [
                'lists' => $listData
            ],
            message: $message
        );
    }
}
