<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps<{
    success?: string;
    error?: string;
}>();
</script>

<template>
    <AdminLayout>
        <Head title="SEO Management" />

        <div class="container mx-auto py-10">
            <h1 class="mb-6 text-3xl font-bold">SEO Management</h1>

            <div v-if="success" class="mb-4 rounded-md bg-green-50 p-4 text-green-800">
                {{ success }}
            </div>

            <div v-if="error" class="mb-4 rounded-md bg-red-50 p-4 text-red-800">
                {{ error }}
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Sitemap</CardTitle>
                        <CardDescription>Generate and submit your sitemap to search engines</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4">Your sitemap helps search engines discover and index your pages.</p>
                        <Button as-child>
                            <a :href="route('seo.generate-sitemap')">Generate & Submit Sitemap</a>
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Meta Tags</CardTitle>
                        <CardDescription>Manage meta tags for your pages</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4">Meta tags help search engines understand your content.</p>
                        <Button as-child>
                            <a :href="route('seo.add-meta-tags')">Manage Meta Tags</a>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div class="mt-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Current Sitemap</CardTitle>
                        <CardDescription>View your current sitemap</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4">Your sitemap is available at: <a :href="route('sitemap')" class="text-blue-600 underline" target="_blank">{{ route('sitemap') }}</a></p>
                        <Button as-child variant="outline">
                            <a :href="route('sitemap')" target="_blank">View Sitemap</a>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template> 