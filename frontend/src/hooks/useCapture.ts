import { useState, useEffect, useRef } from 'react';
import { useRambleStore } from '../stores/useRambleStore';
import { useAuthStore } from '../stores/useAuthStore';
import { rambleService } from '../api/rambles';
import { ProcessedResult, Ramble, UsageLimit } from '../types';

export const useCapture = () => {
    const { user } = useAuthStore();
    const userId = user?.id || 0;
    const contentKey = `rambler_draft_${userId}_content`;
    const idKey = `rambler_draft_${userId}_id`;

    const [content, setContent] = useState(() => localStorage.getItem(contentKey) || '');
    const [currentRambleId, setCurrentRambleId] = useState<number | null>(() => {
        const savedId = localStorage.getItem(idKey);
        return savedId ? parseInt(savedId, 10) : null;
    });
    const [result, setResult] = useState<ProcessedResult | null>(null);
    const [isSaving, setIsSaving] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [usage, setUsage] = useState<UsageLimit | null>(null);

    const { addRamble, updateRamble, fetchRambles, rambles } = useRambleStore();
    const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const fetchUsage = async () => {
        const res = await rambleService.getUsage();
        if (res.success) setUsage(res.data);
    };

    // Reset state when user changes (absolute isolation)
    useEffect(() => {
        const savedContent = localStorage.getItem(contentKey) || '';
        const savedId = localStorage.getItem(idKey);

        setContent(savedContent);
        setCurrentRambleId(savedId ? parseInt(savedId, 10) : null);
        setResult(null);

        fetchRambles();
        fetchUsage();
    }, [userId]);

    // LocalStorage sync
    useEffect(() => {
        if (!userId) return;

        localStorage.setItem(contentKey, content);
        if (currentRambleId) {
            localStorage.setItem(idKey, currentRambleId.toString());
        } else {
            localStorage.removeItem(idKey);
        }
    }, [content, currentRambleId, userId]);

    const syncContent = async (text: string, id: number | null) => {
        if (!text.trim() || isSaving) return id;

        setIsSaving(true);
        try {
            if (id) {
                await updateRamble(id, text);
                return id;
            } else {
                const ramble = await addRamble(text);
                if (ramble) {
                    setCurrentRambleId(ramble.id);
                    return ramble.id;
                }
            }
        } catch (error) {
            console.error('Sync failed', error);
        } finally {
            setIsSaving(false);
        }
        return null;
    };

    // Autosave
    useEffect(() => {
        if (content.length > 5) {
            if (timeoutRef.current) clearTimeout(timeoutRef.current);
            timeoutRef.current = setTimeout(() => {
                syncContent(content, currentRambleId);
            }, 3000);
        }
    }, [content]);

    const handleProcess = async () => {
        if (!content || processing) return;

        setProcessing(true);
        try {
            const rambleId = await syncContent(content, currentRambleId);
            if (rambleId) {
                const res = await rambleService.process(rambleId);
                if (res.success && res.data) {
                    setResult(res.data);
                    fetchRambles();
                    fetchUsage();
                }
            }
        } finally {
            setProcessing(false);
        }
    };

    const handleSelectRamble = async (ramble: Ramble) => {
        if (content.trim() && content !== rambles.find(r => r.id === currentRambleId)?.content) {
            await syncContent(content, currentRambleId);
        }
        setCurrentRambleId(ramble.id);
        setContent(ramble.content);
        setResult(ramble.summary ? {
            summary: ramble.summary,
            topics: ramble.topics || [],
            questions: ramble.questions || [],
            ideas: ramble.ideas || []
        } : null);
    };

    const handleNewRamble = async () => {
        if (content.trim() && content !== rambles.find(r => r.id === currentRambleId)?.content) {
            await syncContent(content, currentRambleId);
        }
        setCurrentRambleId(null);
        setContent('');
        setResult(null);
    };

    return {
        content,
        setContent,
        currentRambleId,
        result,
        isSaving,
        processing,
        usage,
        rambles,
        handleProcess,
        handleSelectRamble,
        handleNewRamble,
        refreshUsage: fetchUsage
    };
};
