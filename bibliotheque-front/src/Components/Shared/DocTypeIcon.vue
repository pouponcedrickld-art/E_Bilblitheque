<script setup lang="ts">
import { computed } from 'vue'
import { Book, FileText, GraduationCap, Newspaper, BookOpen, ClipboardList, ClipboardCheck } from '@lucide/vue'

const props = withDefaults(defineProps<{
  type?: string
  size?: number
}>(), { size: 24 })

const iconMap: Record<string, any> = {
  livre: Book,
  memoire: FileText,
  these: GraduationCap,
  article: Newspaper,
  revue: BookOpen,
  rapport: ClipboardList,
  guide: ClipboardCheck,
}

const iconComponent = computed(() => iconMap[props.type ?? ''] || FileText)
</script>

<template>
  <component :is="iconComponent" :size="size" />
</template>
