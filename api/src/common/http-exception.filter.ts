import { ArgumentsHost, Catch, ExceptionFilter, HttpException, HttpStatus, Logger } from '@nestjs/common';
import { Response } from 'express';

@Catch()
export class HttpExceptionFilter implements ExceptionFilter {
  private readonly logger = new Logger(HttpExceptionFilter.name);

  catch(exception: unknown, host: ArgumentsHost) {
    const ctx = host.switchToHttp();
    const response = ctx.getResponse<Response>();

    const isHttp = exception instanceof HttpException;
    const status = isHttp ? exception.getStatus() : HttpStatus.INTERNAL_SERVER_ERROR;
    const responseBody = isHttp ? exception.getResponse() : undefined;

    let message = 'Internal server error';
    if (typeof responseBody === 'string') {
      message = responseBody;
    } else if (responseBody && typeof responseBody === 'object' && 'message' in responseBody) {
      const m = (responseBody as { message: unknown }).message;
      message = Array.isArray(m) ? m.join(', ') : String(m);
    } else if (exception instanceof Error) {
      message = exception.message;
    }

    if (!isHttp) {
      this.logger.error(exception instanceof Error ? exception.stack : String(exception));
    }

    response.status(status).json({
      error: true,
      data: null,
      message,
    });
  }
}
