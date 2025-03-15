@extends('layouts.app')

@section('title', 'Ối! Trang không tìm thấy - 404 | ' . setting('site_name'))

@section('content')
<div class="error-container">
    <div class="error-content">
        <!-- Main Card -->
        <div class="error-card">
            <!-- Colorful Top Border -->
            <div class="card-border"></div>
            
            <div class="card-body">
                <!-- Header with Error Code -->
                <div class="error-header">
                    <div class="error-code">
                        <span class="error-code-bg">404</span>
                        <div class="error-code-title">Lớp học ảo</div>
                    </div>
                    
                    <!-- Classroom Scene -->
                    <div class="classroom-scene">
                        <!-- Blackboard -->
                        <div class="blackboard">
                            <!-- Chalk Text -->
                            <div class="chalk-text">
                                Trang này đang nghỉ học!
                            </div>
                        </div>
                        
                        <!-- Teacher's Desk -->
                        <div class="teacher-desk">
                            <div class="desk-front"></div>
                        </div>
                        
                        <!-- Student Desks -->
                        <div class="student-desk student-desk-1"></div>
                        <div class="student-desk student-desk-2"></div>
                        <div class="student-desk student-desk-3"></div>
                        <div class="student-desk student-desk-4"></div>
                        
                        <!-- Animated Elements -->
                        <div class="animated-book">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        
                        <div class="animated-chat">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.707 7.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L11 7.586V3a1 1 0 10-2 0v4.586l-.293-.293z" />
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                            </svg>
                        </div>
                        
                        <div class="animated-clock">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.618 5.968l1.453-1.453 1.414 1.414-1.453 1.453a9 9 0 11-1.414-1.414zM12 20a7 7 0 100-14 7 7 0 000 14zM11 8h2v6h-2V8zM8 1h8v2H8V1z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Friendly Message -->
                    <div class="error-message">
                        <h1 class="error-title">Ối! Trang này đang chơi trò trốn tìm!</h1>
                        <p class="error-description">
                            Cô giáo bảo rằng trang bạn đang tìm kiếm giống như một học sinh nghỉ học hôm nay. Không sao đâu, chúng ta có nhiều bài học thú vị khác!
                        </p>
                    </div>
                </div>
                
                <!-- What Happened Section -->
                <div class="info-box">
                    <!-- Decorative Icon -->
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 13l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-3.998 12.078 12.078 0 01.665-6.479L12 13z" />
                            <path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-3.998 12.078 12.078 0 01.665-6.479L12 14zm-8 6h16v2H4v-2z" />
                        </svg>
                    </div>
                    
                    <div class="info-content">
                        <h2 class="info-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            Bài học hôm nay: Lỗi 404 là gì?
                        </h2>
                        
                        <p class="info-text">
                            <span>Lỗi 404</span> xảy ra khi trang web bạn đang tìm kiếm không tồn tại. Giống như khi:
                        </p>
                        
                        <ul class="info-list">
                            <li class="info-list-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Bạn tìm một trang sách đã bị xé mất
                            </li>
                            <li class="info-list-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Bạn tìm một quyển sách trong thư viện nhưng nó đã được mượn
                            </li>
                            <li class="info-list-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Bạn tìm một bạn học trong lớp nhưng bạn ấy nghỉ học hôm nay
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Learning Exercise -->
                <div class="exercise-grid">
                    <!-- What to do now -->
                    <div class="exercise-card task-card">
                        <h3 class="card-title task-title">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Bài tập cho bạn
                        </h3>
                        
                        <ul class="task-list">
                            <li class="task-item">
                                <div class="task-number">1</div>
                                <p class="task-text">Kiểm tra lại địa chỉ trang web - có thể bạn đã gõ sai</p>
                            </li>
                            <li class="task-item">
                                <div class="task-number">2</div>
                                <p class="task-text">Quay trở lại <a href="javascript:history.back()" class="task-link">trang trước đó</a> và thử một đường dẫn khác</p>
                            </li>
                            <li class="task-item">
                                <div class="task-number">3</div>
                                <p class="task-text">Sử dụng tìm kiếm để tìm nội dung bạn cần</p>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Fun Learning Fact -->
                    <div class="exercise-card facts-card">
                        <h3 class="card-title facts-title">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Bạn có biết?
                        </h3>
                        
                        <div class="facts-box">
                            <p class="facts-primary">Mỗi ngày, có hàng triệu lỗi 404 xảy ra trên khắp thế giới internet!</p>
                            <p class="facts-secondary">Điều này cũng giống như việc trong một thư viện lớn, luôn có người đang tìm kiếm những cuốn sách đã được sắp xếp lại hoặc không còn tồn tại.</p>
                        </div>
                        
                        <div class="facts-tip">
                            Tip: Khi bạn gặp lỗi 404, đừng lo lắng! Đó là một phần bình thường của việc khám phá internet.
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-section">
                    <p class="countdown-text">
                        Bạn sẽ được đưa về trang chủ sau 
                        <span id="countdown" class="countdown-number">15</span>
                        giây.
                    </p>
                    
                    <div class="action-buttons">
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            Về trang chủ
                        </a>
                        
                        <button onclick="history.back()" class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Quay lại
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Footer with Fun Learning Element -->
            <div class="error-footer">
                <div class="footer-copyright">
                    <p>© {{ date('Y') }} {{ setting('site_name') }}</p>
                </div>
                <div class="footer-motto">
                    <span>Học mỗi ngày, tiến bộ mỗi ngày</span>
                    <svg class="footer-star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Countdown timer for redirect
    let seconds = 15;
    const countdownElement = document.getElementById('countdown');
    
    const countdownTimer = setInterval(function() {
        seconds--;
        countdownElement.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(countdownTimer);
            window.location.href = "{{ route('home') }}";
        }
    }, 1000);
</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/404.css') }}">
@endpush
