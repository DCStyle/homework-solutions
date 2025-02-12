@extends('layouts.app')

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl font-bold">Bài giải mới nhất</h2>
                    <ul class="list-disc list-inside mt-4">
                        @foreach ($latestPosts as $item)
                            <li class="mb-2">
                                <a title="{{ $item->title }}" href="{{ route('posts.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400">
                                    {{ $item->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 min-w-0 w-full max-xl:p-4 max-md:p2">
            <div class="mt-4 text-lg">
                {!! $content !!}
            </div>
        </div>

        @include('layouts.sidebar-right')
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://atugatran.github.io/FontAwesome6Pro/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/scrape.css') }}">
    <link rel="stylesheet" href="{{ asset('css/wiki.css') }}">
    <link rel="stylesheet" href="{{ asset('css/new_update.css') }}">
    <link rel="stylesheet" href="{{ asset('css/post.css') }}">
@endpush

@push('scripts')
    <script>
        $(document).ready(function (){
            // Dropdown
            $('#dropbtn').on('click', function (){
                $('#dropdown-content').toggleClass('dropdown-content-visible');
            });
            $('#dropdown-content > div').on('click', function (){
                var selectedCatWk = $(this).text();
                var selectedCatId = $(this).data('catid');
                $('#dropbtn').html(selectedCatWk + ' <i class="fa-solid fa-angle-down"></i>');
                $('#dropdown-content').removeClass('dropdown-content-visible');
                console.log('selectedCatId', selectedCatId);
                $('select#wikicat').val(selectedCatId);
                if ($('#wikisearch').val()) {
                    $('form[name="wiki-frm"]').submit();
                }
            });
            $('form[name="wiki-frm"]').on("submit", function (e){
                e.preventDefault();
                if ($('#wikicat').val()) {
                    console.log('test', $('#wikicat').val());
                    this.submit();
                } else {
                    $('#dropdown-content').addClass('dropdown-content-visible');
                }
            });

            // Wiki search
            function changePlaceholderWikiSearchDetail() {
                var wikicatVal = parseInt($('#wikicat').val());
                var wikiSeachPlaceholder = '';
                switch (wikicatVal) {
                    case 1410:
                    case 1413:
                    case 1414:
                        wikiSeachPlaceholder = 'Nhập từ, cụm từ cần tìm';
                        break;
                    case 1411:
                        wikiSeachPlaceholder = 'Nhập câu thành ngữ cần tìm';
                        break;
                    case 1412:
                        wikiSeachPlaceholder = 'Nhập câu ca dao, tục ngữ cần tìm';
                        break;
                    default:
                        wikiSeachPlaceholder = 'Nhập từ, cụm từ cần tìm';
                }
                $('#wikisearch').attr('placeholder', wikiSeachPlaceholder);
            }
            changePlaceholderWikiSearchDetail();
            $('#wikicat').on('change', function (){
                changePlaceholderWikiSearchDetail();
            });

            function removeAccents(str) {
                return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function highlightText(content, searchTerm, contentOrigi) {
                const contentClean = removeAccents(content);
                const searchClean = removeAccents(searchTerm);
                //const regex = new RegExp(`(${searchClean})`, 'gi');
                //const regex = new RegExp(`(${searchClean})(?!\\p{L})`, 'giu');
                const regex = new RegExp(`(?<!\\p{L})(${searchClean})(?!\\p{L})`, 'giu');

                let matches = [...contentClean.matchAll(regex)];

                // Làm nổi bật từng kết quả trong nội dung gốc
                let highlightedContent = content;
                matches.forEach(match => {
                    // Tìm từ tương ứng trong nội dung gốc
                    const originalWord = content.substring(match.index, match.index + match[0].length);
                    console.log(' -- ', originalWord);
                    highlightedContent = highlightedContent.replace(new RegExp(originalWord, 'g'), `<span class="highlight">${originalWord}</span>`);
                });

                return highlightedContent;
            }

            var searchValue = $('#wikisearch').val();
            if (searchValue) {
                $('#wiki-result-list .wiki-result-link').each(function(){
                    var contentText = $(this).text();
                    var contentTextClean = contentText.replace(/[,.-]/g, '');
                    const highlightedContent = highlightText(contentTextClean, searchValue, contentText);
                    $(this).html(highlightedContent);
                });

            }

            $('.item-more-wiki').on('click', function (){
                if ($(this).hasClass('wiki-control-show')) {
                    $(this).closest('.wiki-wrap-articles').find('.itemHide').hide();
                    $(this).removeClass('wiki-control-show').html('Xem thêm <i class="fa-solid fa-angles-down"></i>');
                } else {
                    $(this).closest('.wiki-wrap-articles').find('.itemHide').show();
                    $(this).addClass('wiki-control-show').html('Thu gọn <i class="fa-solid fa-angles-up"></i>');
                }

            });

            $('.wiki-seemore').on('click', function (){
                $('.itemHide').removeClass('itemHide');
                $(this).hide();
            });

            $('.tocItem').on('click', function(e) {
                e.preventDefault();
                var hrefToc = $(this).attr('href')
                hrefToc = hrefToc.replace(/#/g, "")
                var my_element = document.getElementById(hrefToc);
                my_element.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                    inline: "nearest"
                });
            });

            $('#toc-control').on('click', function (e){
                e.preventDefault();
                if ($('.toc-container').height() < 50) {
                    $('.toc-container').css({'height':'auto', 'overflow': 'hidden'});
                    $('#toc-control').html('<i class="fa-sharp fa-solid fa-chevron-up"></i>');
                } else {
                    $('.toc-container').css({'height':'50px', 'overflow': 'hidden'});
                    $('#toc-control').html('<i class="fa-sharp fa-solid fa-chevron-down"></i>');
                }
            });
        });
    </script>
@endpush
