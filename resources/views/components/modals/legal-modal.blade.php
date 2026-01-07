@props([
    'id' => 'legal-modal',
    'title' => 'Legal Information'
])

<div id="{{ $id }}" class="legal-modal" style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center;">
    <!-- Backdrop -->
    <div class="legal-modal-backdrop" style="position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.5);" onclick="closeLegalModal('{{ $id }}')"></div>
    
    <!-- Modal Content -->
    <div class="legal-modal-content" style="position: relative; background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 900px; width: 95%; margin: 1rem; max-height: 85vh; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: #3b82f6;">gavel</span>
                <h3 id="{{ $id }}-title" style="font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; color: #111827; margin: 0;">{{ $title }}</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <!-- Language Switcher -->
                <div id="{{ $id }}-lang-switcher" style="display: none; align-items: center; gap: 0.25rem;">
                    <button type="button" data-lang="en" onclick="switchLegalLanguage('{{ $id }}', 'en')" 
                            style="padding: 0.25rem 0.5rem; font-size: 11px; font-family: Poppins, sans-serif; border: none; border-radius: 4px; cursor: pointer; transition: all 0.2s; background-color: #3b82f6; color: white;">
                        EN
                    </button>
                    <button type="button" data-lang="ms" onclick="switchLegalLanguage('{{ $id }}', 'ms')" 
                            style="padding: 0.25rem 0.5rem; font-size: 11px; font-family: Poppins, sans-serif; border: none; border-radius: 4px; cursor: pointer; transition: all 0.2s; background-color: #f3f4f6; color: #374151;">
                        BM
                    </button>
                    <button type="button" data-lang="zh" onclick="switchLegalLanguage('{{ $id }}', 'zh')" 
                            style="padding: 0.25rem 0.5rem; font-size: 11px; font-family: Poppins, sans-serif; border: none; border-radius: 4px; cursor: pointer; transition: all 0.2s; background-color: #f3f4f6; color: #374151;">
                        中文
                    </button>
                </div>
                <!-- Close Button -->
                <button type="button" onclick="closeLegalModal('{{ $id }}')" style="padding: 0.25rem; color: #6b7280; cursor: pointer; border: none; background: none; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                </button>
            </div>
        </div>
        
        <!-- Body -->
        <div id="{{ $id }}-content" style="padding: 1.5rem; overflow-y: auto; flex: 1;">
            <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
                <!-- Content will be injected via JavaScript -->
            </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 0.75rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: flex-end; flex-shrink: 0;">
            <button type="button" onclick="closeLegalModal('{{ $id }}')" 
                    class="inline-flex items-center px-4 text-xs font-medium rounded transition" 
                    style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #3b82f6; color: white;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">check</span>
                I Understand
            </button>
        </div>
    </div>
</div>