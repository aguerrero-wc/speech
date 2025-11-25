import { Head } from '@inertiajs/react';
import AudioRecorder from '@/components/record-view/AudioRecorder';

export default function Record() {
  return (
    <>
      <Head title="Grabadora de Voz" />
      <AudioRecorder />
    </>
  );
}