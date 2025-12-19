export class User {
    id: number = 0;
    email: string = '';
}

export class Ramble {
    id: number = 0;
    content: string = '';
    word_count: number = 0;
    created_at: string = '';
}

export class ProcessedResult {
    summary: string = '';
    topics: string[] = [];
    questions: string[] = [];
    ideas: string[] = [];
}

export class ApiResponse<T> {
    success: boolean = false;
    data?: T;
    token?: string;
    message?: string;
}
