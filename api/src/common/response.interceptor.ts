import { CallHandler, ExecutionContext, Injectable, NestInterceptor } from '@nestjs/common';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

export interface Envelope<T> {
  error: boolean;
  data: T;
  meta?: Record<string, unknown>;
  message: string;
}

interface PaginatedPayload<T> {
  data: T;
  meta: Record<string, unknown>;
}

function isPaginated<T>(value: unknown): value is PaginatedPayload<T> {
  return (
    typeof value === 'object' &&
    value !== null &&
    'data' in (value as Record<string, unknown>) &&
    'meta' in (value as Record<string, unknown>)
  );
}

@Injectable()
export class ResponseInterceptor<T> implements NestInterceptor<T, Envelope<T>> {
  intercept(_: ExecutionContext, next: CallHandler<T>): Observable<Envelope<T>> {
    return next.handle().pipe(
      map((value) => {
        if (isPaginated<T>(value)) {
          return { error: false, data: value.data, meta: value.meta, message: 'Success' };
        }
        return { error: false, data: value as T, message: 'Success' };
      }),
    );
  }
}
