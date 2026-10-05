<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'subject',
        'content',
        'placeholders',
        'group'
    ];

    /**
     * Parse the template with given data.
     */
    public function parse($data)
    {
        $content = $this->content;
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return [
            'subject' => $subject,
            'content' => $content
        ];
    }
}
