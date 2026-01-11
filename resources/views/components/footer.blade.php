<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-left">
                <span class="footer-copyright">© {{ date('Y') }} ATLINE System. All Rights Reserved.</span>
            </div>
            <div class="footer-right">
                <div class="footer-links">
                    <a href="javascript:void(0)" class="footer-link" onclick="openLegalModal('legal-modal', 'privacy')">Privacy</a>
                    <span class="footer-separator">/</span>
                    <a href="javascript:void(0)" class="footer-link" onclick="openLegalModal('legal-modal', 'terms')">Terms</a>
                    <span class="footer-separator">/</span>
                    <a href="javascript:void(0)" class="footer-link" onclick="openLegalModal('legal-modal', 'disclaimer')">Disclaimer</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Footer responsive styles for mobile/tablet */
    @media (max-width: 480px) {
        .footer .footer-content {
            flex-direction: column !important;
            gap: 0.5rem !important;
            text-align: center !important;
            padding: 0.75rem 0 !important;
        }
        .footer .footer-left,
        .footer .footer-right {
            width: 100%;
            justify-content: center;
        }
        .footer .footer-links {
            justify-content: center;
        }
    }
    @media (min-width: 481px) and (max-width: 768px) {
        .footer .footer-content {
            flex-direction: row !important;
            flex-wrap: wrap;
            justify-content: center !important;
            gap: 0.5rem 1rem !important;
            padding: 0.75rem 0 !important;
        }
    }
</style>

<!-- Legal Modal -->
<x-modals.legal-modal id="legal-modal" />

<!-- Hidden Content Templates - Privacy (Multi-language) -->
<template id="privacy-content-template-en">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.privacy-content-en')
    </div>
</template>

<template id="privacy-content-template-ms">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.privacy-content-ms')
    </div>
</template>

<template id="privacy-content-template-zh">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.privacy-content-zh')
    </div>
</template>

<!-- Hidden Content Templates - Terms (Multi-language) -->
<template id="terms-content-template-en">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.terms-content-en')
    </div>
</template>

<template id="terms-content-template-ms">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.terms-content-ms')
    </div>
</template>

<template id="terms-content-template-zh">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.terms-content-zh')
    </div>
</template>

<!-- Hidden Content Templates - Disclaimer (Multi-language) -->
<template id="disclaimer-content-template-en">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.disclaimer-content-en')
    </div>
</template>

<template id="disclaimer-content-template-ms">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.disclaimer-content-ms')
    </div>
</template>

<template id="disclaimer-content-template-zh">
    <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
        @include('components.legal.disclaimer-content-zh')
    </div>
</template>


<!-- Legal Modal Scripts -->
<script>
    const legalConfig = {
        privacy: { 
            title: 'Privacy Policy', 
            icon: 'security', 
            hasLanguages: true,
            languages: {
                en: { label: 'English', templateId: 'privacy-content-template-en' },
                ms: { label: 'Bahasa Melayu', templateId: 'privacy-content-template-ms' },
                zh: { label: '中文', templateId: 'privacy-content-template-zh' }
            },
            defaultLang: 'en'
        },
        terms: { 
            title: 'Terms of Service', 
            icon: 'description', 
            hasLanguages: true,
            languages: {
                en: { label: 'English', templateId: 'terms-content-template-en' },
                ms: { label: 'Bahasa Melayu', templateId: 'terms-content-template-ms' },
                zh: { label: '中文', templateId: 'terms-content-template-zh' }
            },
            defaultLang: 'en'
        },
        disclaimer: { 
            title: 'Disclaimer', 
            icon: 'info', 
            hasLanguages: true,
            languages: {
                en: { label: 'English', templateId: 'disclaimer-content-template-en' },
                ms: { label: 'Bahasa Melayu', templateId: 'disclaimer-content-template-ms' },
                zh: { label: '中文', templateId: 'disclaimer-content-template-zh' }
            },
            defaultLang: 'en'
        }
    };

    let currentLegalType = null;
    let currentLang = 'en';

    function openLegalModal(modalId, type) {
        const modal = document.getElementById(modalId);
        const config = legalConfig[type];
        currentLegalType = type;
        
        if (modal && config) {
            const titleEl = document.getElementById(modalId + '-title');
            if (titleEl) {
                titleEl.textContent = config.title;
                const iconEl = titleEl.previousElementSibling;
                if (iconEl) iconEl.textContent = config.icon;
            }
            
            // Handle language switcher
            const langSwitcher = document.getElementById(modalId + '-lang-switcher');
            if (config.hasLanguages) {
                if (langSwitcher) {
                    langSwitcher.style.display = 'flex';
                    currentLang = config.defaultLang;
                    updateLanguageButtons(modalId, currentLang);
                }
                loadLegalContent(modalId, config.languages[currentLang].templateId);
            } else {
                if (langSwitcher) langSwitcher.style.display = 'none';
                loadLegalContent(modalId, config.templateId);
            }
            
            modal.style.display = 'flex';
        }
    }

    function loadLegalContent(modalId, templateId) {
        const contentEl = document.getElementById(modalId + '-content');
        const template = document.getElementById(templateId);
        if (contentEl && template) {
            contentEl.innerHTML = template.innerHTML;
        }
    }

    function switchLegalLanguage(modalId, lang) {
        const config = legalConfig[currentLegalType];
        if (config && config.hasLanguages && config.languages[lang]) {
            currentLang = lang;
            loadLegalContent(modalId, config.languages[lang].templateId);
            updateLanguageButtons(modalId, lang);
        }
    }

    function updateLanguageButtons(modalId, activeLang) {
        const buttons = document.querySelectorAll('#' + modalId + '-lang-switcher button');
        buttons.forEach(btn => {
            if (btn.dataset.lang === activeLang) {
                btn.style.backgroundColor = '#3b82f6';
                btn.style.color = 'white';
            } else {
                btn.style.backgroundColor = '#f3f4f6';
                btn.style.color = '#374151';
            }
        });
    }

    function closeLegalModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLegalModal('legal-modal');
    });
</script>