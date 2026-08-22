import { createApiClient } from '@webhatchery/api-client';

const baseUrl = import.meta.env.VITE_API_BASE_URL;
if (!baseUrl) throw new Error('VITE_API_BASE_URL environment variable is required.');

type ApiEnvelope<T> = {
  success?: boolean;
  data: T;
  message?: string;
};
type ApiResponse<T> = { data: ApiEnvelope<T>; status: number };
let tokenResolver: (() => string | null) | null = null;

export const registerAuthTokenResolver = (resolver: (() => string | null) | null) => {
  tokenResolver = resolver;
};

const sharedApi = createApiClient({
  baseURL: baseUrl,
  preserveEnvelope: true,
  tokenProvider: () => tokenResolver?.() ?? null,
});

const request = async <T>(method: string, endpoint: string, body?: unknown): Promise<ApiResponse<T>> => ({
  data: await sharedApi.request<ApiEnvelope<T>>(endpoint, { method, body }),
  status: 200,
});

const api = {
  get: <T>(endpoint: string) => request<T>('GET', endpoint),
  post: <T>(endpoint: string, body?: unknown) => request<T>('POST', endpoint, body),
  put: <T>(endpoint: string, body?: unknown) => request<T>('PUT', endpoint, body),
  patch: <T>(endpoint: string, body?: unknown) => request<T>('PATCH', endpoint, body),
  delete: <T>(endpoint: string) => request<T>('DELETE', endpoint),
};

export default api;
