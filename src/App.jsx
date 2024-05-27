import { Route,createBrowserRouter,createRoutesFromElements, RouterProvider  } from "react-router-dom"
import HomePage from "./pages/HomePage"
import JobsPge from "./pages/JobsPage";
import NavbarLayout from "./layouts/NavbarLayout"
import NotFound from "./pages/NotFound";


function App() {
  const router = createBrowserRouter(
  createRoutesFromElements (
  <Route path="/"  element={<NavbarLayout />} >
    <Route path="/"  element={<HomePage />} />
    <Route path="/jobs"  element={<JobsPge />} />
    <Route path="*"  element={<NotFound />} />
  </Route>

)
  );
  return  < RouterProvider router={router} />
  
};

export default App