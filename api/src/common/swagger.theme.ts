export function buildCustomCss(logoUrl: string): string {
  return customCss(logoUrl);
}

export const customCss = (logoUrl = './public/logo.png') => `
  /* ── Background & Layout ────────────────────────────────────────────── */
  body {
    background-color: #F4F7F9 !important;
  }
  .swagger-ui {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
  }
  
  /* ── Topbar ─────────────────────────────────────────────────────────── */
  .swagger-ui .topbar {
    background-color: #264653 !important;
    border-bottom: 3px solid #2a9d8f !important;
  }
  .swagger-ui .topbar a span {
    display: none !important; /* Hide the 'Swagger UI' text */
  }
  /* Hide the useless URL search bar in the topbar */
  .swagger-ui .topbar .download-url-wrapper {
    display: none !important;
  }
  /* Replace default Swagger logo with Feng Office */
  .swagger-ui .topbar a svg {
    display: none !important;
  }
  .swagger-ui .topbar a {
    display: flex;
    align-items: center;
  }
  .swagger-ui .topbar a::before {
    content: '';
    display: inline-block;
    width: 200px;
    height: 45px;
    background: url('${logoUrl}') no-repeat left center;
    background-size: contain;
    /* Add a subtle brightness filter if the dark text doesn't contrast well against the #264653 topbar */
    filter: brightness(0) invert(1);
  }

  /* ── Buttons & Accents ──────────────────────────────────────────────── */
  .swagger-ui .btn.execute {
    background-color: #2a9d8f !important;
    border-color: #2a9d8f !important;
    color: #fff !important;
    box-shadow: 0 2px 4px rgba(42, 157, 143, 0.2) !important;
  }
  .swagger-ui .btn.execute:hover {
    background-color: #218276 !important;
  }
  .swagger-ui .btn.authorize {
    border-color: #2a9d8f !important;
    color: #2a9d8f !important;
  }
  .swagger-ui .btn.authorize svg {
    fill: #2a9d8f !important;
  }

  /* ── Info Section typography defaults for light mode ────────────────── */
  .swagger-ui .info a {
    color: #2a9d8f !important;
  }

  /* ── HTTP Method Badges ─────────────────────────────────────────────── */
  .swagger-ui .opblock.opblock-post .opblock-summary-method {
    background-color: #2a9d8f !important; 
  }
  .swagger-ui .opblock.opblock-get .opblock-summary-method {
    background-color: #264653 !important;
  }
  .swagger-ui .opblock.opblock-patch .opblock-summary-method {
    background-color: #e9c46a !important; 
    color: #FFF !important; 
  }
  .swagger-ui .opblock.opblock-delete .opblock-summary-method {
    background-color: #e76f51 !important; 
  }

  /* ── Scheme Container (where the Authorize button lives) ───────────── */
  .swagger-ui .scheme-container {
    background-color: transparent !important;
    box-shadow: none !important;
    border-bottom: 1px solid rgba(0,0,0,0.1) !important;
  }

  /* ── Auto-Dark Mode Support (@media prefers-color-scheme: dark) ────── */
  @media (prefers-color-scheme: dark) {
    body, .swagger-ui { 
      background-color: #1B1B1D !important; 
      color: #F4F7F9 !important; 
    }

    /* Target all text elements that are black by default in Swagger */
    .swagger-ui .info h1, .swagger-ui .info h2, .swagger-ui .info p, 
    .swagger-ui .info a, .swagger-ui .info li,
    .swagger-ui .opblock-tag, 
    .swagger-ui .opblock .opblock-summary-operation-id, 
    .swagger-ui .opblock .opblock-summary-path, 
    .swagger-ui .opblock .opblock-summary-path__deprecated, 
    .swagger-ui .opblock-summary-description, 
    .swagger-ui .parameter__name, 
    .swagger-ui .parameter__type, .swagger-ui .parameter__in, 
    .swagger-ui table thead tr th, 
    .swagger-ui .response-col_status, 
    .swagger-ui .response-col_description, 
    .swagger-ui .model-title, 
    .swagger-ui .model-toggle, 
    .swagger-ui .prop-type, 
    .swagger-ui .prop-format,
    .swagger-ui .tab li {
      color: #F4F7F9 !important;
    }

    .swagger-ui .scheme-container { 
      border-bottom: 1px solid #333 !important; 
    }
    .swagger-ui .opblock { 
      border: 1px solid #333 !important; 
      background-color: rgba(255, 255, 255, 0.03) !important; 
    }

    /* Modals & Inputs */
    .swagger-ui select { 
      background-color: #2b2b2e !important; 
      color: #F4F7F9 !important; 
      border: 1px solid #444 !important; 
    }
    .swagger-ui input[type=text], .swagger-ui input[type=password], 
    .swagger-ui input[type=search], .swagger-ui input[type=email], 
    .swagger-ui input[type=file], .swagger-ui textarea {
      background-color: #2b2b2e !important; 
      color: #F4F7F9 !important; 
      border: 1px solid #444 !important;
    }
    
    .swagger-ui .dialog-ux .modal-ux { 
      background-color: #1B1B1D !important; 
      border: 1px solid #444 !important; 
    }
    .swagger-ui .dialog-ux .modal-ux-header h3, 
    .swagger-ui .dialog-ux .modal-ux-content h4 { 
      color: #F4F7F9 !important; 
    }
    .swagger-ui .dialog-ux .modal-ux-header .close-modal { 
      fill: #F4F7F9 !important; 
    }

    /* Code blocks / Models response blocks */
    .swagger-ui .model-box, .swagger-ui .microlight { 
      background-color: #2b2b2e !important; 
      border-radius: 4px; 
    }
    .swagger-ui .responses-inner h4, .swagger-ui .responses-inner h5 {
      color: #F4F7F9 !important;
    }
  }
`;
