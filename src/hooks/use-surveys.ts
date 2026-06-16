import { useEffect, useState } from "react";
import { listSurveys, getSurvey, subscribe, type Survey } from "@/lib/mock-data";

export function useSurveys(): Survey[] {
  const [list, setList] = useState<Survey[]>(() => listSurveys());
  useEffect(() => {
    setList(listSurveys());
    return subscribe(() => setList(listSurveys()));
  }, []);
  return list;
}

export function useSurvey(id: string): Survey | undefined {
  const [s, setS] = useState<Survey | undefined>(() => getSurvey(id));
  useEffect(() => {
    setS(getSurvey(id));
    return subscribe(() => setS(getSurvey(id)));
  }, [id]);
  return s;
}
