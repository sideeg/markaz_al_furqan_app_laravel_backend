{{-- FIXED: HifzLog Index with Better Search --}}
{{-- Now searches full names anywhere in text, not just first letters --}}

@extends('layouts.admin')

@section('title', 'سجلات الحفظ')

@section('content')

{{-- Include Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
    }
    
    .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
        border-color: #86b7fe;
    }
</style>

<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <h3>📖 سجلات الحفظ</h3>
</div>

<div class="admin-card">
    <div class="card-header">
        {{-- ENHANCED SEARCHABLE FILTERS --}}
        <form method="GET" action="{{ route('admin.hifz_logs.index') }}" class="row g-3">
            
            {{-- Course Filter (Searchable) --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📚 الدورة</label>
                <select name="course_id" class="form-select searchable-select" id="course_filter">
                    <option value="">-- اختر الدورة --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" 
                                {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sheikh Filter (Searchable) --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">🧔 الشيخ</label>
                <select name="sheikh_id" class="form-select searchable-select" id="sheikh_filter">
                    <option value="">-- اختر الشيخ --</option>
                    @foreach($sheikhs as $sheikh)
                        <option value="{{ $sheikh->id }}" 
                                {{ request('sheikh_id') == $sheikh->id ? 'selected' : '' }}>
                            {{ $sheikh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Student Filter (Searchable) --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">🧑‍🎓 الطالب</label>
                <select name="student_id" class="form-select searchable-select" id="student_filter">
                    <option value="">-- اختر الطالب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" 
                                {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Surah Filter --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📖 السورة</label>
                <input type="text" name="surah" class="form-control" 
                       placeholder="ابحث عن السورة..." value="{{ request('surah') }}">
            </div>

            {{-- Start Date --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📅 من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>

            {{-- End Date --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📅 إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>

            {{-- Action Buttons --}}
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter"></i> بحث
                </button>
                <a href="{{ route('admin.hifz_logs.index') }}" class="btn btn-secondary flex-grow-1">
                    <i class="fas fa-redo"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <div class="card-body">
        {{-- Logs Table --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الطالب</th>
                        <th>الشيخ</th>
                        <th>الدورة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>التقييم</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        {{-- ✅ FIXED: session_date (not date) --}}
                        <td>{{ $log->session_date?->format('Y-m-d') ?? 'غير محدد' }}</td>
                        
                        <td>{{ $log->student?->name ?? 'محذوف' }}</td>
                        <td>{{ $log->sheikh?->name ?? 'محذوف' }}</td>
                        
                        {{-- ✅ SHOWS COURSE (with soft-delete indicator) --}}
                        <td>
                            {{ $log->course?->name ?? 'محذوفة' }}
                            @if($log->course?->deleted_at)
                                <span class="badge bg-danger ms-1" title="هذه الدورة محذوفة">محذوفة</span>
                            @endif
                        </td>
                        
                        {{-- ✅ FIXED: start_surah & end_surah (not surah) --}}
                        <td>{{ $log->start_surah }}:{{ $log->start_ayah }}</td>
                        <td>{{ $log->end_surah }}:{{ $log->end_ayah }}</td>
                        
                        {{-- Evaluation Badge --}}
                        <td>
                            <span class="badge 
                                @switch($log->evaluation)
                                    @case('excellent')
                                        bg-success
                                        @break
                                    @case('very_good')
                                        bg-info
                                        @break
                                    @case('good')
                                        bg-primary
                                        @break
                                    @case('needs_improvement')
                                        bg-warning
                                        @break
                                    @case('poor')
                                        bg-danger
                                        @break
                                @endswitch
                            ">
                                @switch($log->evaluation)
                                    @case('excellent')
                                        ممتاز 🌟
                                        @break
                                    @case('very_good')
                                        جيد جداً 👍
                                        @break
                                    @case('good')
                                        جيد ✓
                                        @break
                                    @case('needs_improvement')
                                        يحتاج تحسين 📈
                                        @break
                                    @case('poor')
                                        ضعيف ⚠️
                                        @break
                                @endswitch
                            </span>
                        </td>
                        
                        {{-- ✅ FIXED: notes (not comment) --}}
                        <td>
                            <small title="{{ $log->notes }}">
                                {{ Str::limit($log->notes, 20) ?? '-' }}
                            </small>
                        </td>
                        
                        {{-- Actions --}}
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.hifz_logs.show', $log) }}" 
                               class="btn btn-sm btn-info" title="عرض التفاصيل">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.hifz_logs.edit', $log) }}" 
                               class="btn btn-sm btn-warning" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.hifz_logs.destroy', $log) }}" 
                                  method="POST" class="d-inline" 
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <p class="text-muted mb-0">لا توجد سجلات مطابقة.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>

@section('scripts')
{{-- 1. Ensure jQuery is loaded FIRST if your layout doesn't already have it --}}
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

{{-- 2. Include Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        
        // Helper function to normalize Arabic text for better searching
        function normalizeArabic(text) {
            if (!text) return '';
            return text
                .replace(/[أإآ]/g, 'ا')     // Unify Alif
                .replace(/ة/g, 'ه')         // Unify Taa Marbuta/Haa
                .replace(/[ًٌٍَُِّْ]/g, '')   // Remove Tashkeel (Harakat)
                .toLowerCase();             // Handle English chars just in case
        }

        // Custom matcher function for Arabic Search
        function customMatcher(params, data) {
            // If there are no search terms, return all of the data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Do not display the item if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // Normalize both the search term and the database text
            var term = normalizeArabic(params.term);
            var text = normalizeArabic(data.text);

            // Search anywhere in the text
            if (text.indexOf(term) > -1) {
                return data;
            }

            return null;
        }

        // Initialize Select2 for all searchable selects
        $('.searchable-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'اكتب للبحث... (مثال: علي أحمد)',
            allowClear: true,
            width: '100%',
            matcher: customMatcher,  // Using the Arabic-friendly matcher
            language: {
                noResults: function() {
                    return 'لم يتم العثور على نتائج';
                },
                searching: function() {
                    return 'جاري البحث...';
                }
            }
        });
    });
</script>
@endsection

@endsection