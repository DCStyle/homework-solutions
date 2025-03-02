<?php

namespace Database\Seeders;

use App\Models\FooterColumn;
use App\Models\FooterLink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear existing data
        FooterLink::truncate();
        FooterColumn::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Column 1: Về Chúng Tôi (About Us)
        $column1 = FooterColumn::create([
            'title' => 'Về Chúng Tôi',
            'position' => 0,
            'is_active' => true,
        ]);

        $column1Links = [
            ['title' => 'Giới thiệu trung tâm', 'url' => '#'],
            ['title' => 'Đội ngũ giáo viên', 'url' => '#'],
            ['title' => 'Cơ sở vật chất', 'url' => '#'],
            ['title' => 'Liên hệ', 'url' => '#'],
        ];

        foreach ($column1Links as $index => $link) {
            FooterLink::create([
                'footer_column_id' => $column1->id,
                'title' => $link['title'],
                'url' => $link['url'],
                'position' => $index,
                'is_active' => true,
            ]);
        }

        // Column 2: Khóa Học (Courses)
        $column2 = FooterColumn::create([
            'title' => 'Khóa Học',
            'position' => 1,
            'is_active' => true,
        ]);

        $column2Links = [
            ['title' => 'Tiếng Anh cho trẻ em', 'url' => '#'],
            ['title' => 'Tiếng Anh cho người đi làm', 'url' => '#'],
            ['title' => 'Luyện thi IELTS', 'url' => '#'],
            ['title' => 'Luyện thi TOEIC', 'url' => '#'],
        ];

        foreach ($column2Links as $index => $link) {
            FooterLink::create([
                'footer_column_id' => $column2->id,
                'title' => $link['title'],
                'url' => $link['url'],
                'position' => $index,
                'is_active' => true,
            ]);
        }

        // Column 3: Tài Nguyên Học Tập (Learning Resources)
        $column3 = FooterColumn::create([
            'title' => 'Tài Nguyên Học Tập',
            'position' => 2,
            'is_active' => true,
        ]);

        $column3Links = [
            ['title' => 'Ngữ pháp Tiếng Anh', 'url' => '#'],
            ['title' => 'Từ vựng theo chủ đề', 'url' => '#'],
            ['title' => 'Bài tập trực tuyến', 'url' => '#'],
            ['title' => 'Tài liệu miễn phí', 'url' => '#'],
        ];

        foreach ($column3Links as $index => $link) {
            FooterLink::create([
                'footer_column_id' => $column3->id,
                'title' => $link['title'],
                'url' => $link['url'],
                'position' => $index,
                'is_active' => true,
            ]);
        }

        // Column 4: Hỗ Trợ Học Viên (Student Support)
        $column4 = FooterColumn::create([
            'title' => 'Hỗ Trợ Học Viên',
            'position' => 3,
            'is_active' => true,
        ]);

        $column4Links = [
            ['title' => 'Hướng dẫn đăng ký', 'url' => '#'],
            ['title' => 'Câu hỏi thường gặp', 'url' => '#'],
            ['title' => 'Chính sách học phí', 'url' => '#'],
            ['title' => 'Lịch khai giảng', 'url' => '#'],
        ];

        foreach ($column4Links as $index => $link) {
            FooterLink::create([
                'footer_column_id' => $column4->id,
                'title' => $link['title'],
                'url' => $link['url'],
                'position' => $index,
                'is_active' => true,
            ]);
        }
    }
}
