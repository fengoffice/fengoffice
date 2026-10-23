// Prisma raw queries return BigInt for COUNT(*) and unsigned INT columns.
// Patching toJSON here is the standard fix for NestJS + Prisma — JSON.stringify
// calls toJSON on each value before serializing, so this converts BigInts to
// Number before they reach the serializer without affecting any other behavior.
// All BigInts in this process come from MySQL; no third-party library uses
// BigInt.prototype.toJSON for its own purposes.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
(BigInt.prototype as any).toJSON = function () { return Number(this); };

import { NestFactory, Reflector } from '@nestjs/core';
import { NestExpressApplication } from '@nestjs/platform-express';
import { ValidationPipe } from '@nestjs/common';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';
import { join } from 'path';
import helmet from 'helmet';
import { ResponseInterceptor } from './common/response.interceptor';
import { HttpExceptionFilter } from './common/http-exception.filter';
import { JwtAuthGuard } from './auth/jwt-auth.guard';
import { customCss } from './common/swagger.theme';
import { bootstrapRuntimeConfig, getApiPort, getAppName, getAppLogoUrl } from './config/runtime-config';

async function bootstrap() {
  bootstrapRuntimeConfig();
  const { AppModule } = await import('./app.module.js');
  const isDev = process.env.NODE_ENV !== 'production';
  const app = await NestFactory.create<NestExpressApplication>(AppModule, {
    logger: isDev
      ? ['log', 'warn', 'error', 'debug', 'verbose']
      : ['log', 'warn', 'error'],
  });

  // Serve static assets from the `/public` directory (e.g. your logo.png)
  app.useStaticAssets(join(__dirname, '..', 'public'), {
    prefix: '/public/',
  });

  // Trust the front proxy so req.ip / rate-limits / generated absolute URLs
  // behave correctly when Apache forwards /fengoffice/api -> localhost:3000.
  app.set('trust proxy', 1);

  // ── Security headers (Helmet) ──────────────────────────────────────────────
  // Sets X-Content-Type-Options, X-Frame-Options, Strict-Transport-Security, etc.
  // Content-Security-Policy is relaxed to allow Swagger UI's inline scripts/styles.
  app.use(
    helmet({
      contentSecurityPolicy: {
        directives: {
          defaultSrc: ["'self'"],
          scriptSrc: ["'self'", "'unsafe-inline'", 'cdn.jsdelivr.net'],
          styleSrc: ["'self'", "'unsafe-inline'", 'cdn.jsdelivr.net'],
          imgSrc: ["'self'", 'data:', 'cdn.jsdelivr.net'],
          connectSrc: ["'self'"],
        },
      },
    }),
  );

  // ── CORS ───────────────────────────────────────────────────────────────────
  // By default CORS is disabled (same-origin only). Set CORS_ORIGINS in .env to
  // enable cross-origin requests, e.g. CORS_ORIGINS=https://app.example.com
  // Use CORS_ORIGINS=* to allow all origins (development only).
  const rawOrigins = process.env.CORS_ORIGINS?.trim();
  if (rawOrigins) {
    const origins =
      rawOrigins === '*'
        ? true
        : rawOrigins.split(',').map((o) => o.trim()).filter(Boolean);
    app.enableCors({
      origin: origins,
      methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
      allowedHeaders: ['Authorization', 'Content-Type'],
      credentials: true,
    });
  }

  app.useGlobalPipes(new ValidationPipe({ whitelist: true, transform: true }));
  app.useGlobalInterceptors(new ResponseInterceptor());
  app.useGlobalFilters(new HttpExceptionFilter());
  app.useGlobalGuards(new JwtAuthGuard(app.get(Reflector)));

  // ── Swagger / OpenAPI ──────────────────────────────────────────────────────
  // Must be set up BEFORE app.listen() so Express registers the routes.
  // The reverse proxy owns the external `/fengoffice/api` prefix, so NestJS
  // exposes routes without an internal `/api` prefix.

  // Derive the public-facing base path from Feng's ROOT_URL so Swagger UI
  // generates correct curl commands when accessed behind the Apache proxy.
  // Example: ROOT_URL="http://localhost/fengoffice" → server path="/fengoffice/api"
  // Falls back to "/" when running directly on port 3000 (no proxy).
  const fengRootUrl = process.env.FENG_ROOT_URL?.trim();
  const proxyServerPath = fengRootUrl
    ? `${new URL(fengRootUrl).pathname.replace(/\/$/, '')}/api`
    : '/';

  const appName = getAppName();
  const appLogoUrl = getAppLogoUrl();

  const swaggerConfig = new DocumentBuilder()
    .setTitle(`${appName} API`)
    .setDescription(
      `REST API for ${appName} — reads/writes the shared MySQL database.<br /><br />` +
      'All endpoints except `/auth/login` and `/auth/refresh` require a Bearer JWT.<br /><br />' +
      'Every response is wrapped in `{ error, data, meta?, message }`.',
    )
    .setVersion('1.0')
    .addServer(proxyServerPath, 'Via Apache reverse proxy')
    .addServer('/', 'Direct (port 3000)')
    .addBearerAuth(
      { type: 'http', scheme: 'bearer', bearerFormat: 'JWT' },
      'access-token',
    )
    .build();

  const document = SwaggerModule.createDocument(app, swaggerConfig);
  SwaggerModule.setup('docs', app, document, {
    swaggerOptions: { persistAuthorization: true },
    customCss: customCss(appLogoUrl),
    customfavIcon: './public/favicon.ico',
    customSiteTitle: `${appName} API Docs`,
    // Exclude swagger assets from the global JSON response envelope by serving
    // them via Express before NestJS interceptors run.
    useGlobalPrefix: false,
  });
  // ──────────────────────────────────────────────────────────────────────────

  await app.listen(getApiPort());
}
bootstrap().catch((error: unknown) => {
  const message = error instanceof Error ? error.stack ?? error.message : String(error);
  console.error(message);
  process.exit(1);
});
